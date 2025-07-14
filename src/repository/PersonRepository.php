<?php

namespace src\repository;
use Doctrine\ORM\EntityRepository;
use src\models\Person; 

/**
 * @extends EntityRepository<Person>
 */ 
class PersonRepository extends EntityRepository{

     public function findByName(string $name): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.name LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            ->getQuery()
            ->getResult();
    }
}