<?php

namespace App\Entity;

use App\Enum\EtatJoker;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

// JokerInstance = une instance spécifique d'un joker dans UNE partie (avec son état, sa position, son compteur)
// C'est le joker "vivant" dans une partie, alors que JokerTemplate est juste le modèle
#[ORM\Entity]
#[ORM\Table(name: 'joker_instance')]
class JokerInstance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Le modèle (template) de ce joker (quel type de joker c'est : Vampire, Baron, etc.)
    #[ORM\ManyToOne(targetEntity: JokerTemplate::class, inversedBy: 'instances')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le template de joker est obligatoire")]
    private ?JokerTemplate $jokerTemplate = null;

    // La partie dans laquelle se trouve ce joker
    #[ORM\ManyToOne(targetEntity: Partie::class, inversedBy: 'jokers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "La partie est obligatoire")]
    private ?Partie $partie = null;

    // État spécial du joker : foil, holographique, polychrome, négatif (null = état normal)
    #[ORM\Column(type: 'string', enumType: EtatJoker::class, nullable: true)]
    private ?EtatJoker $etat = null;

    // Position du joker dans la rangée (1 = premier slot, 2 = deuxième slot, etc.)
    #[Assert\Positive(message: "L'ordre doit être positif")]
    #[ORM\Column(type: 'integer')]
    private int $ordre = 1;

    // Compteur de stack pour ce joker spécifique (ex: Vampire avec 10 stacks = +150 chips)
    #[Assert\PositiveOrZero(message: "Le compteur de stack doit être positif ou zéro")]
    #[ORM\Column(type: 'integer')]
    private int $compteurStack = 0;

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJokerTemplate(): ?JokerTemplate
    {
        return $this->jokerTemplate;
    }

    public function setJokerTemplate(?JokerTemplate $jokerTemplate): self
    {
        $this->jokerTemplate = $jokerTemplate;
        return $this;
    }

    public function getPartie(): ?Partie
    {
        return $this->partie;
    }

    public function setPartie(?Partie $partie): self
    {
        $this->partie = $partie;
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

    public function getOrdre(): int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): self
    {
        $this->ordre = $ordre;
        return $this;
    }

    public function getCompteurStack(): int
    {
        return $this->compteurStack;
    }

    public function setCompteurStack(int $compteurStack): self
    {
        $this->compteurStack = $compteurStack;
        return $this;
    }

    /**
     * Méthode helper pour incrémenter le stack
     */
    public function incrementStack(int $amount = 1): self
    {
        $this->compteurStack += $amount;
        return $this;
    }

    /**
     * Méthode helper pour obtenir la valeur totale de l'effet
     * basée sur le template et le compteur de stack
     */
    public function getEffetTotal(): float
    {
        if (!$this->jokerTemplate) {
            return 0;
        }

        // Les jokers sans stack retournent 0
        if ($this->jokerTemplate->getTypeStack() === \App\Enum\TypeStack::NONE) {
            return 0;
        }

        return $this->jokerTemplate->getStackParUnite() * $this->compteurStack;
    }

    /**
     * Vérifie si ce joker utilise un système de stacks modifiable
     */
    public function hasManualStack(): bool
    {
        if (!$this->jokerTemplate) {
            return false;
        }

        return $this->jokerTemplate->getTypeStack() !== \App\Enum\TypeStack::NONE;
    }

    /**
     * Méthode helper pour obtenir le nom complet avec état
     */
    public function getNomComplet(): string
    {
        $nom = $this->jokerTemplate?->getNom() ?? 'Inconnu';
        
        if ($this->etat) {
            $nom .= ' (' . ucfirst($this->etat->value) . ')';
        }

        return $nom;
    }
}
