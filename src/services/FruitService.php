<?php

namespace src\services;
use Doctrine\ORM\EntityManagerInterface;
use src\models\Fruit;

class FruitService
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function createFruit(string $name): Fruit
    {
        echo $name;
        $fruit = new Fruit($name);
        $this->entityManager->persist($fruit);
        $this->entityManager->flush();

        return $fruit;
    }

   public function getAllFruits(): array
    {
        $fruits = $this->entityManager->getRepository(Fruit::class)->findAll();

        return array_map(function (Fruit $fruit) {
            return [
                'id' => $fruit->getId(),
                'name' => $fruit->getName(),
            ];
        }, $fruits);
    }
    public function findFruit(int $id): ?Fruit
    {
        return $this->entityManager->getRepository(Fruit::class)->find($id);
    }
}