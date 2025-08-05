<?php

namespace src\models\DTO;

class PersonFruitDTO
{
    private int $id;
    private string $firstName;
    private string $lastName;
    private array $preferredFruits;

    public function __construct(int $id, string $firstName, string $lastName, array $preferredFruits = [])
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->preferredFruits = $preferredFruits;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'preferredFruits' => $this->preferredFruits,
        ];
    }

}