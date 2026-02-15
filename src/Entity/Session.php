<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'session')]
class Session
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: "L'identifiant de la session est obligatoire")]
    #[Assert\Length(
        max: 100,
        maxMessage: "L'identifiant ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $identifiant = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\PositiveOrZero(message: "Le montant d'argent doit être positif ou zéro")]
    private int $money = 0;

    #[ORM\Column(type: 'integer')]
    #[Assert\Positive(message: "Le nombre de cartes en main doit être positif")]
    private int $hand = 8;

    #[ORM\Column(type: 'integer')]
    #[Assert\PositiveOrZero(message: "Le nombre de défausses doit être positif ou zéro")]
    private int $discard = 3;

    // Relation ManyToOne vers User
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'sessions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'utilisateur est obligatoire")]
    private ?User $user = null;

    // Relation OneToOne vers HandLevel
    #[ORM\OneToOne(targetEntity: HandLevel::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?HandLevel $handLevel = null;

    // Relation OneToOne vers Voucher
    #[ORM\OneToOne(targetEntity: Voucher::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Voucher $voucher = null;

    /**
     * @var Collection<int, Carte>
     */
    #[ORM\ManyToMany(targetEntity: Carte::class, inversedBy: 'sessions')]
    #[ORM\JoinTable(name: 'session_carte')]
    private Collection $cartes;

    /**
     * @var Collection<int, Joker>
     */
    #[ORM\ManyToMany(targetEntity: Joker::class)]
    #[ORM\JoinTable(name: 'session_joker')]
    private Collection $jokers;

    /**
     * @var Collection<int, Consommable>
     */
    #[ORM\ManyToMany(targetEntity: Consommable::class, inversedBy: 'sessions')]
    #[ORM\JoinTable(name: 'session_consommable')]
    private Collection $consommables;

    public function __construct()
    {
        $this->cartes = new ArrayCollection();
        $this->jokers = new ArrayCollection();
        $this->consommables = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentifiant(): ?string
    {
        return $this->identifiant;
    }

    public function setIdentifiant(string $identifiant): static
    {
        $this->identifiant = $identifiant;
        return $this;
    }

    public function getMoney(): int
    {
        return $this->money;
    }

    public function setMoney(int $money): static
    {
        $this->money = $money;
        return $this;
    }

    public function getHand(): int
    {
        return $this->hand;
    }

    public function setHand(int $hand): static
    {
        $this->hand = $hand;
        return $this;
    }

    public function getDiscard(): int
    {
        return $this->discard;
    }

    public function setDiscard(int $discard): static
    {
        $this->discard = $discard;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getHandLevel(): ?HandLevel
    {
        return $this->handLevel;
    }

    public function setHandLevel(HandLevel $handLevel): static
    {
        $this->handLevel = $handLevel;
        return $this;
    }

    public function getVoucher(): ?Voucher
    {
        return $this->voucher;
    }

    public function setVoucher(?Voucher $voucher): static
    {
        $this->voucher = $voucher;
        return $this;
    }

    /**
     * @return Collection<int, Carte>
     */
    public function getCartes(): Collection
    {
        return $this->cartes;
    }

    public function addCarte(Carte $carte): static
    {
        if (!$this->cartes->contains($carte)) {
            $this->cartes->add($carte);
        }
        return $this;
    }

    public function removeCarte(Carte $carte): static
    {
        $this->cartes->removeElement($carte);
        return $this;
    }

    /**
     * @return Collection<int, Joker>
     */
    public function getJokers(): Collection
    {
        return $this->jokers;
    }

    public function addJoker(Joker $joker): static
    {
        if (!$this->jokers->contains($joker)) {
            $this->jokers->add($joker);
        }
        return $this;
    }

    public function removeJoker(Joker $joker): static
    {
        $this->jokers->removeElement($joker);
        return $this;
    }

    /**
     * @return Collection<int, Consommable>
     */
    public function getConsommables(): Collection
    {
        return $this->consommables;
    }

    public function addConsommable(Consommable $consommable): static
    {
        if (!$this->consommables->contains($consommable)) {
            $this->consommables->add($consommable);
        }
        return $this;
    }

    public function removeConsommable(Consommable $consommable): static
    {
        $this->consommables->removeElement($consommable);
        return $this;
    }
}
