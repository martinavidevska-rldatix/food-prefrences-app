<?php

namespace src\cache;

use Predis\Client;
use src\models\DTO\PersonFruitDTO;


class RedisPersonCache implements IPersonCache
{
    private Client $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public function getPeopleByFirstName(string $key): ?array
    {
        $data = $this->redis->get($key);
        return $data ? unserialize($data) : null;
    }

    public function storePeopleByFirstName(string $key, array $people): void
    {
        $this->redis->setex($key, 600, serialize($people)); // 10 minutes
    }

    public function getPerson(string $personId): ?array
    {
        $key = 'person_' . $personId;
        $dataLoadedFromCache = $this->redis->get($key);
        return $dataLoadedFromCache ? unserialize($dataLoadedFromCache) : null;
    }

    public function storePerson(PersonFruitDTO $personFruitDTO): void
    {
        if ($personFruitDTO->getId() !== null) {
            $key = 'person_' . $personFruitDTO->getId();
            $this->redis->setex($key, 60, serialize($personFruitDTO->toArray()));
        }
    }
}
