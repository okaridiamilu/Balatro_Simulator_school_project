<?php

namespace App\Entity;

use App\Enum\CarteColor;
use App\Enum\CarteNumber;
use App\Enum\CarteStatus;
use App\Enum\CarteStatusMatter;
use App\Enum\CarteStatusSeal;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'carte')]
class Carte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', enumType: CarteNumber::class)]
    #[Assert\NotNull(message: "Le numéro de la carte est obligatoire")]
    private ?CarteNumber $number = null;

    #[ORM\Column(type: 'string', enumType: CarteColor::class)]
    #[Assert\NotNull(message: "La couleur de la carte est obligatoire")]
    private ?CarteColor $color = null;

    #[ORM\Column(type: 'string', enumType: CarteStatus::class)]
    #[Assert\NotNull(message: "Le statut de la carte est obligatoire")]
    private ?CarteStatus $status = null;

    #[ORM\Column(type: 'string', enumType: CarteStatusSeal::class)]
    #[Assert\NotNull(message: "Le sceau de la carte est obligatoire")]
    private ?CarteStatusSeal $seal = null;

    #[ORM\Column(type: 'string', enumType: CarteStatusMatter::class)]
    #[Assert\NotNull(message: "La matière de la carte est obligatoire")]
    private ?CarteStatusMatter $matter = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $image = null;

    /**
     * @var Collection<int, Partie>
     */
    #[ORM\ManyToMany(targetEntity: Partie::class, mappedBy: 'cartes')]
    private Collection $parties;

    public function __construct()
    {
        $this->parties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?CarteNumber
    {
        return $this->number;
    }

    public function setNumber(CarteNumber $number): static
    {
        $this->number = $number;
        return $this;
    }

    public function getColor(): ?CarteColor
    {
        return $this->color;
    }

    public function setColor(CarteColor $color): static
    {
        $this->color = $color;
        return $this;
    }

    public function getStatus(): ?CarteStatus
    {
        return $this->status;
    }

    public function setStatus(CarteStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getSeal(): ?CarteStatusSeal
    {
        return $this->seal;
    }

    public function setSeal(CarteStatusSeal $seal): static
    {
        $this->seal = $seal;
        return $this;
    }

    public function getMatter(): ?CarteStatusMatter
    {
        return $this->matter;
    }

    public function setMatter(CarteStatusMatter $matter): static
    {
        $this->matter = $matter;
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
            $partie->addCarte($this);
        }
        return $this;
    }

    public function removePartie(Partie $partie): static
    {
        if ($this->parties->removeElement($partie)) {
            $partie->removeCarte($this);
        }
        return $this;
    }
}
