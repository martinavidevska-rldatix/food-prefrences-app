<?php
namespace src\services;

use Doctrine\ORM\EntityManager;
use src\models\Person;
use src\models\Fruit;
use src\cache\IPersonCache;

class PersonService
{
    private EntityManager $em;
    private IPersonCache $personCache;

    public function __construct(EntityManager $em, IPersonCache $personCache)
    {
        $this->em = $em;
         $this->personCache = $personCache;
    }
    public function getAllPeople(): array
    {
        $people = $this->em->getRepository(Person::class)->findAll();

        return array_map(function (Person $person) {
            return [
                'id' => $person->getId(),
                'firstName' => $person->getFirstName(),
                'lastName' => $person->getLastName(),
                'preferredFruits' => array_map(
                    fn(Fruit $fruit) => $fruit->getName(),
                    $person->getPreferredFruits()->toArray()
                )
            ];
        }, $people);
    }

    public function findPerson(int $id): ?array
    {
        $person = $this->em->find(Person::class, $id);
        if (!$person) return null;

        return [
            'id' => $person->getId(),
            'firstName' => $person->getFirstName(),
            'lastName' => $person->getLastName(),
        ];
    }

    public function getPreferredFruits(int $id): array
    {
        $person = $this->em->find(Person::class, $id);
        if (!$person) return [];

        return array_map(fn(Fruit $fruit) => [
            'id' => $fruit->getId(),
            'name' => $fruit->getName()
        ], $person->getPreferredFruits()->toArray());
    }

    public function createPerson(string $firstName, string $lastName): array
    {
        $person = new Person();
        $refl = new \ReflectionClass($person);
        $refl->getProperty('firstName')->setValue($person, $firstName);
        $refl->getProperty('lastName')->setValue($person, $lastName);

        $this->em->persist($person);
        $this->em->flush();

        return [
            'id' => $person->getId(),
            'firstName' => $person->getFirstName(),
            'lastName' => $person->getLastName()
        ];
    }

    public function addPreferredFruit(int $personId, int $fruitId): void
        {
            $person = $this->em->getRepository(Person::class)->find($personId);
            $fruit = $this->em->getRepository(Fruit::class)->find($fruitId);

            if (!$person || !$fruit) {
                throw new \Exception("Person or Fruit not found.");
            }

            $person->addPreferredFruit($fruit);
            $this->em->persist($person);
            $this->em->flush();
        }

    public function getAllPeopleWithFruits(): array
    {
        $people = $this->em->getRepository(Person::class)->findAll();

        return array_map(function (Person $person) {
            return [
                'person_id' => $person->getId(),
                'firstName' => $person->getFirstName(),
                'lastName' => $person->getLastName(),
                'preferredFruits' => array_map(
                    fn($fruit) => [
                        'fruit_id' => $fruit->getId(),
                        'name' => $fruit->getName()
                    ],
                    $person->getPreferredFruits()->toArray()
                )
            ];
        }, $people);
    }

    public function searchByFirstName(string $name): array
    {
        $cacheKey = 'person_search_' . strtolower(trim($name));

        // ✅ Try to fetch from Redis first
        $cached = $this->personCache->getPeopleByFirstName($cacheKey);
        if ($cached !== null) {
            error_log("✅ Cache hit for: $cacheKey");
            return $cached;
        }

        // ❌ Cache miss
        error_log("❌ Cache miss for: $cacheKey — querying DB.");

        $repository = $this->em->getRepository(Person::class);
        $query = $repository->createQueryBuilder('p')
            ->where('LOWER(p.firstName) LIKE :name')
            ->setParameter('name', '%' . strtolower($name) . '%')
            ->getQuery();

        $people = $query->getResult();

        $results = array_map(function ($person) {
            return [
                'id' => $person->getId(),
                'firstName' => $person->getFirstName(),
                'lastName' => $person->getLastName(),
                'preferredFruits' => array_map(
                    fn(Fruit $fruit) => [
                        'id' => $fruit->getId(),
                        'name' => $fruit->getName()
                    ],
                    $person->getPreferredFruits()->toArray()
                )
            ];
        }, $people);

        // ✅ Store results in cache for next time
        $this->personCache->storePeopleByFirstName($cacheKey, $results);

        return $results;
    }
       

}
