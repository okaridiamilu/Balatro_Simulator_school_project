<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

// HandLevel = les niveaux de toutes les mains de poker (pair, brelan, quinte, etc.) pour une partie
// Chaque niveau augmente les chips et mult de la main correspondante
#[ORM\Entity]
#[ORM\Table(name: 'hand_level')]
class HandLevel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\PositiveOrZero(message: "Le niveau doit être positif ou zéro")]
    private int $pair = 0;

    #[ORM\Column(type: 'integer')]
    #[Assert\PositiveOrZero(message: "Le niveau doit être positif ou zéro")]
    private int $twoPair = 0;

    #[ORM\Column(type: 'integer')]
    #[Assert\PositiveOrZero(message: "Le niveau doit être positif ou zéro")]
    private int $threeOfAKind = 0;

    #[ORM\Column(type: 'integer')]
    #[Assert\PositiveOrZero(message: "Le niveau doit être positif ou zéro")]
    private int $straight = 0;

    #[ORM\Column(type: 'integer')]
    #[Assert\PositiveOrZero(message: "Le niveau doit être positif ou zéro")]
    private int $flush = 0;

    #[ORM\Column(type: 'integer')]
    #[Assert\PositiveOrZero(message: "Le niveau doit être positif ou zéro")]
    private int $fullHouse = 0;

    #[ORM\Column(type: 'integer')]
    #[Assert\PositiveOrZero(message: "Le niveau doit être positif ou zéro")]
    private int $fourOfAKind = 0;

    #[ORM\Column(type: 'integer')]
    #[Assert\PositiveOrZero(message: "Le niveau doit être positif ou zéro")]
    private int $straightFlush = 0;

    #[ORM\Column(type: 'integer')]
    #[Assert\PositiveOrZero(message: "Le niveau doit être positif ou zéro")]
    private int $royalFlush = 0;

    #[ORM\Column(type: 'integer')]
    #[Assert\PositiveOrZero(message: "Le niveau doit être positif ou zéro")]
    private int $highCard = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPair(): int
    {
        return $this->pair;
    }

    public function setPair(int $pair): static
    {
        $this->pair = $pair;
        return $this;
    }

    public function getTwoPair(): int
    {
        return $this->twoPair;
    }

    public function setTwoPair(int $twoPair): static
    {
        $this->twoPair = $twoPair;
        return $this;
    }

    public function getThreeOfAKind(): int
    {
        return $this->threeOfAKind;
    }

    public function setThreeOfAKind(int $threeOfAKind): static
    {
        $this->threeOfAKind = $threeOfAKind;
        return $this;
    }

    public function getStraight(): int
    {
        return $this->straight;
    }

    public function setStraight(int $straight): static
    {
        $this->straight = $straight;
        return $this;
    }

    public function getFlush(): int
    {
        return $this->flush;
    }

    public function setFlush(int $flush): static
    {
        $this->flush = $flush;
        return $this;
    }

    public function getFullHouse(): int
    {
        return $this->fullHouse;
    }

    public function setFullHouse(int $fullHouse): static
    {
        $this->fullHouse = $fullHouse;
        return $this;
    }

    public function getFourOfAKind(): int
    {
        return $this->fourOfAKind;
    }

    public function setFourOfAKind(int $fourOfAKind): static
    {
        $this->fourOfAKind = $fourOfAKind;
        return $this;
    }

    public function getStraightFlush(): int
    {
        return $this->straightFlush;
    }

    public function setStraightFlush(int $straightFlush): static
    {
        $this->straightFlush = $straightFlush;
        return $this;
    }

    public function getRoyalFlush(): int
    {
        return $this->royalFlush;
    }

    public function setRoyalFlush(int $royalFlush): static
    {
        $this->royalFlush = $royalFlush;
        return $this;
    }

    public function getHighCard(): int
    {
        return $this->highCard;
    }

    public function setHighCard(int $highCard): static
    {
        $this->highCard = $highCard;
        return $this;
    }
}
