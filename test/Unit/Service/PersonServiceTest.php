<?php

namespace test\Unit\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use src\cache\RedisPersonCache;
use src\models\DTO\PersonDTO;
use src\models\Person;
use src\repository\PersonRepository;
use src\services\FruitService;
use src\services\PersonService;

class PersonServiceTest extends TestCase
{
    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->personRepository = $this->createMock(PersonRepository::class);
        $this->fruitService = $this->createMock(FruitService::class);
        $this->personCache = $this->createMock(RedisPersonCache::class);
        $this->em = $this->createMock(EntityManager::class);
        $this->personService = new PersonService($this->em, $this->personCache, $this->fruitService, $this->personRepository);
    }

    /**
     * @throws Exception
     */
    public function testGetAllPeopleReturnsAllPeopleWithMappedData()
    {
        $person1 = $this->createMock(Person::class);
        $person1->method('getId')->willReturn(1);
        $person1->method('getFirstName')->willReturn('John');
        $person1->method('getLastName')->willReturn('Doe');
        $person1->method('getPreferredFruits')->willReturn(new ArrayCollection([]));

        $person2 = $this->createMock(Person::class);
        $person2->method('getId')->willReturn(2);
        $person2->method('getFirstName')->willReturn('Jane');
        $person2->method('getLastName')->willReturn('Smith');
        $person2->method('getPreferredFruits')->willReturn(new ArrayCollection([]));

        $this->personRepository->method('findAll')->willReturn([$person1, $person2]);

        $result = $this->personService->getAllPeople();

        $this->assertCount(2, $result);
        $this->assertEquals('John', $result[0]['firstName']);
        $this->assertEquals('Jane', $result[1]['firstName']);
    }

    /**
     * @throws Exception
     */
  public function testCreatePerson()
    {
        $firstName = 'John';
        $lastName = 'Doe';

        $this->em->expects($this->once())->method('persist')->with($this->isInstanceOf(Person::class))
            ->willReturnCallback(function ($person) {
                $reflection = new \ReflectionClass($person);
                $property = $reflection->getProperty('id');
                $property->setValue($person, 123);
            });
        $this->em->expects($this->once())->method('flush');

        $result = $this->personService->createPerson($firstName, $lastName);

        $this->assertInstanceOf(PersonDTO::class, $result);
        $this->assertEquals($firstName, $result->getFirstName());
        $this->assertEquals($lastName, $result->getLastName());
        $this->assertEquals(123, $result->getId());
    }

    /**
     * @throws Exception
     */
    public function testReturnsPersonWhenFound()
    {
        $personId = 1;
        $person = $this->createMock(Person::class);

        $this->personRepository->expects($this->once())
            ->method('find')
            ->with($personId)
            ->willReturn($person);

        $result = $this->personService->findPerson($personId);

        $this->assertSame($person, $result);
    }

    public function testThrowsExceptionWhenPersonNotFound()
    {
        $personId = 999;

        $this->personRepository->expects($this->once())
            ->method('find')
            ->with($personId)
            ->willReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Person not found with ID $personId");

        $this->personService->findPerson($personId);
    }

    /**
     * @throws Exception
     */
    public function testDeletesPersonSuccessfully()
    {
        $personId = 1;
        $person = $this->createMock(Person::class);

        $this->personRepository->expects($this->once())
            ->method('find')
            ->with($personId)
            ->willReturn($person);

        $this->em->expects($this->once())->method('remove')->with($person);
        $this->em->expects($this->once())->method('flush');

        $this->personService->deletePerson($personId);
    }

    public function testThrowsExceptionWhenDeletingNonExistentPerson()
    {
        $personId = 999;

        $this->personRepository->expects($this->once())
            ->method('find')
            ->with($personId)
            ->willReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Person not found with ID $personId");

        $this->personService->deletePerson($personId);
    }
    public function testReturnsCachedResultsWhenCacheHit()
    {
        $name = 'John';
        $cacheKey = 'person_search_john';
        $cachedResults = [
            ['id' => 1, 'firstName' => 'John', 'lastName' => 'Doe', 'preferredFruits' => []]
        ];

        $this->personCache->expects($this->once())
            ->method('getPeopleByFirstName')
            ->with($cacheKey)
            ->willReturn($cachedResults);

        $this->personRepository->expects($this->never())->method('findByFirstName');

        $result = $this->personService->searchByFirstName($name);

        $this->assertSame($cachedResults, $result);
    }

    public function testQueriesDatabaseAndStoresInCacheWhenCacheMiss()
    {
        $name = 'Jane';
        $cacheKey = 'person_search_jane';
        $person = $this->createMock(\src\models\Person::class);
        $person->method('getId')->willReturn(2);
        $person->method('getFirstName')->willReturn('Jane');
        $person->method('getLastName')->willReturn('Smith');
        $person->method('getPreferredFruits')->willReturn(new \Doctrine\Common\Collections\ArrayCollection([]));

        $this->personCache->expects($this->once())
            ->method('getPeopleByFirstName')
            ->with($cacheKey)
            ->willReturn(null);

        $this->personRepository->expects($this->once())
            ->method('findByFirstName')
            ->with($name)
            ->willReturn([$person]);

        $this->personCache->expects($this->once())
            ->method('storePeopleByFirstName')
            ->with($cacheKey, [
                ['id' => 2, 'firstName' => 'Jane', 'lastName' => 'Smith', 'preferredFruits' => []]
            ]);

        $result = $this->personService->searchByFirstName($name);

        $this->assertCount(1, $result);
        $this->assertEquals('Jane', $result[0]['firstName']);
    }

    public function testReturnsEmptyArrayWhenNoResultsFound()
    {
        $name = 'NonExistent';
        $cacheKey = 'person_search_nonexistent';

        $this->personCache->expects($this->once())
            ->method('getPeopleByFirstName')
            ->with($cacheKey)
            ->willReturn(null);

        $this->personRepository->expects($this->once())
            ->method('findByFirstName')
            ->with($name)
            ->willReturn([]);

        $this->personCache->expects($this->once())
            ->method('storePeopleByFirstName')
            ->with($cacheKey, []);

        $result = $this->personService->searchByFirstName($name);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}