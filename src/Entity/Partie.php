<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

// Partie = une session de jeu complète (avec son deck, ses jokers, son argent, etc.)
#[ORM\Entity]
#[ORM\Table(name: 'partie')]
class Partie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: "L'identifiant de la partie est obligatoire")]
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

    #[ORM\Column(type: 'integer')]
    #[Assert\Positive(message: "Le nombre de slots de jokers doit être positif")]
    private int $jokerSlots = 5;

    #[ORM\Column(type: 'boolean')]
    private bool $observatoireActif = false;

    // L'utilisateur qui possède cette partie (chaque partie appartient à quelqu'un)
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'parties')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'utilisateur est obligatoire")]
    private ?User $user = null;

    // Les niveaux de toutes les mains (pair, brelan, quinte, etc.) pour cette partie
    #[ORM\OneToOne(targetEntity: HandLevel::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?HandLevel $handLevel = null;

    // Le voucher actif pour cette partie (bonus spécial, optionnel)
    #[ORM\OneToOne(targetEntity: Voucher::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Voucher $voucher = null;

    // Le deck de cartes de cette partie (52 cartes de base + celles ajoutées)
    #[ORM\ManyToMany(targetEntity: Carte::class, inversedBy: 'parties')]
    #[ORM\JoinTable(name: 'partie_carte')]
    private Collection $cartes;

    // Les instances de jokers de cette partie (chaque joker a son propre état et compteur)
    #[ORM\OneToMany(targetEntity: JokerInstance::class, mappedBy: 'partie', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['ordre' => 'ASC'])]
    private Collection $jokers;

    // Les consommables de cette partie (tarots, planètes, spectres)
    #[ORM\ManyToMany(targetEntity: Consommable::class, inversedBy: 'parties')]
    #[ORM\JoinTable(name: 'partie_consommable')]
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

    public function getJokerSlots(): int
    {
        return $this->jokerSlots;
    }

    public function setJokerSlots(int $jokerSlots): static
    {
        $this->jokerSlots = $jokerSlots;
        return $this;
    }

    /**
     * Calcule le nombre de slots de jokers utilisés
     * TOUS les jokers (négatifs inclus) prennent de la place physiquement
     */
    public function getUsedJokerSlots(): int
    {
        return $this->jokers->count();
    }

    /**
     * Retourne le nombre total de slots disponibles (base + bonus des négatifs)
     */
    public function getTotalJokerSlots(): int
    {
        $negatifJokers = 0;

        foreach ($this->jokers as $joker) {
            if ($joker->getEtat() === \App\Enum\EtatJoker::NEGATIF) {
                $negatifJokers++;
            }
        }

        return $this->jokerSlots + $negatifJokers;
    }

    /**
     * Retourne le nombre de slots de jokers disponibles
     */
    public function getAvailableJokerSlots(): int
    {
        return $this->getTotalJokerSlots() - $this->getUsedJokerSlots();
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
     * @return Collection<int, JokerInstance>
     */
    public function getJokers(): Collection
    {
        return $this->jokers;
    }

    public function addJoker(JokerInstance $joker): static
    {
        if (!$this->jokers->contains($joker)) {
            $this->jokers->add($joker);
            $joker->setPartie($this);
        }
        return $this;
    }

    public function removeJoker(JokerInstance $joker): static
    {
        if ($this->jokers->removeElement($joker)) {
            if ($joker->getPartie() === $this) {
                $joker->setPartie(null);
            }
        }
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

    public function isObservatoireActif(): bool
    {
        return $this->observatoireActif;
    }

    public function setObservatoireActif(bool $observatoireActif): static
    {
        $this->observatoireActif = $observatoireActif;
        return $this;
    }
}
