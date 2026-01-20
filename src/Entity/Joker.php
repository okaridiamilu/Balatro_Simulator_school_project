<?php

namespace App\Entity;

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

    #[Assert\NotBlank(message: "L'état doit être défini")]
    #[Assert\Choice(
        choices: ['normale', 'foil', 'polychrome', 'chromatique'],
        message: "L'état doit être : normale, foil, polychrome ou chromatique"
    )]
    #[ORM\Column(length: 20, nullable: false)]
    private ?string $etat = 'normale';

    #[Assert\NotBlank(message: "La rareté doit être définie")]
    #[Assert\Choice(
        choices: ['commun', 'uncommon', 'rare', 'legendary'],
        message: "La rareté doit être : commun, uncommon, rare ou legendary"
    )]
    #[ORM\Column(length: 20, nullable: false)]
    private ?string $rarete = 'commun';

    #[Assert\NotBlank(message: "La description ne peut pas être vide")]
    #[Assert\Length(
        min: 10,
        max: 500,
        minMessage: "La description doit contenir au moins {{ limit }} caractères",
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères"
    )]
    #[ORM\Column(length: 500, nullable: false)]
    private ?string $description = null;

    #[Assert\NotBlank(message: "L'effet doit être défini")]
    #[Assert\Length(
        min: 5,
        max: 300,
        minMessage: "L'effet doit contenir au moins {{ limit }} caractères"
    )]
    #[ORM\Column(length: 300, nullable: false)]
    private ?string $effet = null;

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

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(?string $etat): self
    {
        $this->etat = $etat;
        return $this;
    }

    public function getRarete(): ?string
    {
        return $this->rarete;
    }

    public function setRarete(?string $rarete): self
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

    public function getEffet(): ?string
    {
        return $this->effet;
    }

    public function setEffet(?string $effet): self
    {
        $this->effet = $effet;
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
