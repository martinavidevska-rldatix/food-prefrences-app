<?php
namespace src\cache;

use src\models\DTO\PersonFruitDTO;
use src\models\Person;

interface IPersonCache
{
    public function getPeopleByFirstName(string $key): ?array;
    public function storePeopleByFirstName(string $key, array $people): void;
    public function getPerson(string $personId): ?array;
    public function storePerson(PersonFruitDTO $personFruitDTO): void;
}
