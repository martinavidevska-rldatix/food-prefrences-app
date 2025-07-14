<?php
namespace src\cache;

use src\models\Person;

interface IPersonCache
{
    public function getPeopleByFirstName(string $key): ?array;
    public function storePeopleByFirstName(string $key, array $people): void;
}
