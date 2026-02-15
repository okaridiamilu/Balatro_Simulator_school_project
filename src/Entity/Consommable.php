<?php

namespace App\Entity;

use App\Enum\ConsommableCategory;
use App\Enum\ConsommableStatus;
use App\Enum\ConsommableType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'consommable')]
class Consommable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: "Le nom du consommable est obligatoire")]
    #[Assert\Length(
        max: 100,
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $name = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: "La description est obligatoire")]
    private ?string $description = null;

    #[ORM\Column(type: 'string', enumType: ConsommableCategory::class)]
    #[Assert\NotNull(message: "La catégorie est obligatoire")]
    private ?ConsommableCategory $category = null;

    #[ORM\Column(type: 'string', enumType: ConsommableType::class)]
    #[Assert\NotNull(message: "Le type est obligatoire")]
    private ?ConsommableType $type = null;

    #[ORM\Column(type: 'string', enumType: ConsommableStatus::class)]
    #[Assert\NotNull(message: "Le statut est obligatoire")]
    private ?ConsommableStatus $status = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $image = null;

    /**
     * @var Collection<int, Partie>
     */
    #[ORM\ManyToMany(targetEntity: Partie::class, mappedBy: 'consommables')]
    private Collection $parties;

    public function __construct()
    {
        $this->parties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getCategory(): ?ConsommableCategory
    {
        return $this->category;
    }

    public function setCategory(ConsommableCategory $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getType(): ?ConsommableType
    {
        return $this->type;
    }

    public function setType(ConsommableType $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getStatus(): ?ConsommableStatus
    {
        return $this->status;
    }

    public function setStatus(ConsommableStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @return Collection<int, Partie>
     */
    public function getParties(): Collection
    {
        return $this->parties;
    }

    public function addPartie(Partie $partie): static
    {
        if (!$this->parties->contains($partie)) {
            $this->parties->add($partie);
            $partie->addConsommable($this);
        }
        return $this;
    }

    public function removePartie(Partie $partie): static
    {
        if ($this->parties->removeElement($partie)) {
            $partie->removeConsommable($this);
        }
        return $this;
    }
}
