<?php

namespace src\repository;
use Doctrine\ORM\EntityRepository;
use src\models\Person; 
use Doctrine\ORM\EntityManager;


/**
 * @extends EntityRepository<Person>
 */ 
class PersonRepository extends EntityRepository{

     public function findByFirstName(string $name): array
    {
        return $this->createQueryBuilder('p')
            ->where('LOWER(p.firstName) LIKE :name')
            ->setParameter('name', '%' . strtolower($name) . '%')
            ->getQuery()
            ->getResult();

    }
}