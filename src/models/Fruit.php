<?php

namespace src\models;

use Doctrine\ORM\Mapping as ORM;
use src\repository\FruitRepository;

#[ORM\Entity(repositoryClass: FruitRepository::class)]
#[ORM\Table(name: 'fruits')]
class Fruit
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(type: 'string')]
    private string $name;


    public function __construct(string $name)
    {
        $this->name = $name;
    }

    // Getters and setters
    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

}
