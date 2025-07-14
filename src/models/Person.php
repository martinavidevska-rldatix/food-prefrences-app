<?php
namespace src\models;

use Collator;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use src\models\Fruit;

#[ORM\Entity]
#[ORM\Table(name: 'people')]
class Person
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(name: 'firstName', type: 'string')]
    private string $firstName;

    #[ORM\Column(name: 'lastName', type: 'string')]
    private string $lastName;

      #[ORM\ManyToMany(targetEntity: Fruit::class)]
    #[ORM\JoinTable(name: "people_fruits",
        joinColumns: [new ORM\JoinColumn(name: "person_id", referencedColumnName: "id")],
        inverseJoinColumns: [new ORM\JoinColumn(name: "fruit_id", referencedColumnName: "id")]
    )]
    private Collection $preferredFruits;

    public function __construct()
    {
        $this->preferredFruits = new ArrayCollection();
    }

    // Getters and setters
    public function getId(): int { return $this->id; }
    public function getFirstName(): string { return $this->firstName; }
    public function getLastName(): string { return $this->lastName; }

    public function getPreferredFruits():Collection 
    {
        return $this->preferredFruits;
    }
    public function addPreferredFruit(Fruit $fruit): void
    {
        if (!$this->preferredFruits->contains($fruit)) {
            $this->preferredFruits->add($fruit);
        }
    }

}
