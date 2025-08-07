<?php

namespace src\services;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use src\models\DTO\PersonDTO;
use src\models\DTO\PersonFruitDTO;
use src\models\Person;
use src\models\Fruit;
use src\cache\IPersonCache;
use src\repository\PersonRepository;

class PersonService
{
    private EntityManager $em;
    private IPersonCache $personCache;
    private PersonRepository $personRepository;
    private FruitService $fruitService;

    public function __construct(EntityManager $em, IPersonCache $personCache, PersonRepository $personRepository, FruitService $fruitService)
    {
        $this->em = $em;
        $this->personCache = $personCache;
        $this->personRepository = $personRepository;
        $this->fruitService = $fruitService;
    }

    private function mapPersonToDTO(Person $person): PersonDTO
    {
        return new PersonDTO(
            $person->getId(),
            $person->getFirstName(),
            $person->getLastName(),
        );
    }

    private function mapFruitToArray(Fruit $fruit): array
    {
        return [
            'id' => $fruit->getId(),
            'name' => $fruit->getName()
        ];
    }

    private function mapPersonWithFruitsToArray(Person $person): array
    {
        return [
            'id' => $person->getId(),
            'firstName' => $person->getFirstName(),
            'lastName' => $person->getLastName(),
            'preferredFruits' => array_map([$this, 'mapFruitToArray'], $person->getPreferredFruits()->toArray())
        ];
    }


    /** @return Person[] */
    public function getAllPeople(): array
    {
        $people = $this->personRepository->findAll();
        return array_map([$this, 'mapPersonWithFruitsToArray'], $people);
    }

    /**
     * @throws \Exception
     */
    public function findPerson(int $id): ?Person
    {
        $person = $this->personRepository->find($id);
        if (!$person) {
            throw new \Exception("Person not found with ID $id");
        }
        return $person;
    }

    /**
     * @throws \Exception
     */
    private function getPreferredFruitsForPerson(int $personId): array
    {
        $person = $this->findPerson($personId);
        return array_map([$this, 'mapFruitToArray'], $person->getPreferredFruits()->toArray());
    }

    /**
     * @throws \Exception
     */
    public function getPersonWithFruits(int $id): array
    {
        $cached = $this->personCache->getPerson($id);
        if ($cached !== null) {
            return $cached;
        }
        $person = $this->findPerson($id);
        $preferredFruits = $this->getPreferredFruitsForPerson($id);

        $dto = new PersonFruitDto(
            $person->getId(),
            $person->getFirstName(),
            $person->getLastName(),
            $preferredFruits
        );
        $this->personCache->storePerson($dto);

        return $dto->toArray();

    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function createPerson(string $firstName, string $lastName): PersonDTO
    {
        $person = new Person();
        $person->setFirstName($firstName);
        $person->setLastName($lastName);

        $this->em->persist($person);
        $this->em->flush();

        return new PersonDTO(id: $person->getId(), firstName: $person->getFirstName(), lastName: $person->getLastName());
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws \Exception
     */
    public function deletePerson(int $id): void
    {
        $person = $this->findPerson($id);
        $this->em->remove($person);
        $this->em->flush();
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws \Exception
     */
    public function updatePerson(int $id, array $data): array
    {
        $person = $this->findPerson($id);
        $person->setFirstName($data['firstName']);
        $person->setLastName($data['lastName']);

        $this->em->persist($person);
        $this->em->flush();
        return $this->mapPersonToDTO($person)->toArray();
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function addPreferredFruit(int $personId, int $fruitId): void
    {
        $person = $this->findPerson($personId);
        $fruit = $this->fruitService->findFruit($fruitId);

        $person->addPreferredFruit($fruit);
        $this->em->persist($person);
        $this->em->flush();
    }

    public function searchByFirstName(string $name): array
    {
        $cacheKey = 'person_search_' . strtolower(trim($name));

        $cached = $this->personCache->getPeopleByFirstName($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $people = $this->personRepository->findByFirstName($name);

        $results = array_map([$this, 'mapPersonWithFruitsToArray'], $people);

        $this->personCache->storePeopleByFirstName($cacheKey, $results);

        return $results;
    }
}
