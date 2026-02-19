<?php

namespace App\Entity;

use App\Enum\RareteJoker;
use App\Enum\TypeStack;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

// JokerTemplate = le modèle/catalogue d'un joker (Vampire, Baron, etc.) avec ses caractéristiques de base
// Pas une instance spécifique, juste la "recette" du joker
#[ORM\Entity]
#[ORM\Table(name: 'joker_template')]
class JokerTemplate
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
    #[ORM\Column(length: 50, nullable: false, unique: true)]
    private ?string $nom = null;

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
    #[ORM\Column(type: 'text', nullable: false)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    // Code identifiant l'effet de ce joker (ex: "vampire", "baron", "constellation")
    // Utilisé pour savoir quelle logique appliquer
    #[Assert\NotBlank(message: "Le code d'effet ne peut pas être vide")]
    #[Assert\Length(max: 50)]
    #[ORM\Column(length: 50, nullable: false)]
    private ?string $effetCode = null;

    // Configuration JSON pour quand l'effet se déclenche
    // Exemple: {"poker_hand": "Full House", "card_count": 5}
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $conditionActivation = null;

    // Type de stack : chips, mult_flat (mult additif), mult_multiplicateur, xmult
    #[Assert\NotNull(message: "Le type de stack doit être défini")]
    #[ORM\Column(type: 'string', enumType: TypeStack::class)]
    private ?TypeStack $typeStack = null;

    /**
     * Valeur ajoutée par unité de stack
     * Exemple: 15 pour +15 chips, 0.25 pour x0.25 mult
     */
    #[Assert\NotNull(message: "La valeur par stack doit être définie")]
    #[Assert\PositiveOrZero]
    #[ORM\Column(type: 'float')]
    private ?float $stackParUnite = null;

    /**
     * @var Collection<int, JokerInstance>
     */
    #[ORM\OneToMany(targetEntity: JokerInstance::class, mappedBy: 'jokerTemplate')]
    private Collection $instances;

    public function __construct()
    {
        $this->instances = new ArrayCollection();
        $this->conditionActivation = [];
    }

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

    public function getEffetCode(): ?string
    {
        return $this->effetCode;
    }

    public function setEffetCode(?string $effetCode): self
    {
        $this->effetCode = $effetCode;
        return $this;
    }

    public function getConditionActivation(): ?array
    {
        return $this->conditionActivation;
    }

    public function setConditionActivation(?array $conditionActivation): self
    {
        $this->conditionActivation = $conditionActivation;
        return $this;
    }

    public function getTypeStack(): ?TypeStack
    {
        return $this->typeStack;
    }

    public function setTypeStack(?TypeStack $typeStack): self
    {
        $this->typeStack = $typeStack;
        return $this;
    }

    public function getStackParUnite(): ?float
    {
        return $this->stackParUnite;
    }

    public function setStackParUnite(?float $stackParUnite): self
    {
        $this->stackParUnite = $stackParUnite;
        return $this;
    }

    /**
     * @return Collection<int, JokerInstance>
     */
    public function getInstances(): Collection
    {
        return $this->instances;
    }

    public function addInstance(JokerInstance $instance): self
    {
        if (!$this->instances->contains($instance)) {
            $this->instances->add($instance);
            $instance->setJokerTemplate($this);
        }

        return $this;
    }

    public function removeInstance(JokerInstance $instance): self
    {
        if ($this->instances->removeElement($instance)) {
            if ($instance->getJokerTemplate() === $this) {
                $instance->setJokerTemplate(null);
            }
        }

        return $this;
    }
}
