<?php

namespace App\Entity;

use App\Enum\EtatJoker;
use App\Enum\RareteJoker;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Joker
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Le nom du joker ne peut pas être vide")]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères"
    )]
    #[ORM\Column(length: 50, nullable: false)]
    private ?string $nom = null;

    #[Assert\NotNull(message: "L'état doit être défini")]
    #[ORM\Column(type: 'string', enumType: EtatJoker::class)]
    private ?EtatJoker $etat = null;

    #[Assert\NotNull(message: "La rareté doit être définie")]
    #[ORM\Column(type: 'string', enumType: RareteJoker::class)]
    private ?RareteJoker $rarete = null;

    #[Assert\NotBlank(message: "La description ne peut pas être vide")]
    #[Assert\Length(
        min: 10,
        max: 500,
        minMessage: "La description doit contenir au moins {{ limit }} caractères",
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères"
    )]
    #[ORM\Column(length: 500, nullable: false)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getEtat(): ?EtatJoker
    {
        return $this->etat;
    }

    public function setEtat(?EtatJoker $etat): self
    {
        $this->etat = $etat;
        return $this;
    }

    public function getRarete(): ?RareteJoker
    {
        return $this->rarete;
    }

    public function setRarete(?RareteJoker $rarete): self
    {
        $this->rarete = $rarete;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }
}
