<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

// User = un utilisateur du site (avec son nom d'utilisateur et mot de passe hashé)
#[ORM\Entity]
#[ORM\Table(name: 'user')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    #[Assert\NotBlank(message: "Le nom d'utilisateur est obligatoire")]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: "Le nom d'utilisateur doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le nom d'utilisateur ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $username = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "Le mot de passe est obligatoire")]
    private ?string $password = null;

    // Toutes les parties créées par cet utilisateur (un user peut avoir plusieurs parties)
    #[ORM\OneToMany(targetEntity: Partie::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $parties;

    public function __construct()
    {
        $this->parties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
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
            $partie->setUser($this);
        }
        return $this;
    }

    public function removePartie(Partie $partie): static
    {
        if ($this->parties->removeElement($partie)) {
            // set the owning side to null (unless already changed)
            if ($partie->getUser() === $this) {
                $partie->setUser(null);
            }
        }
        return $this;
    }

    /**
     * Méthodes requises par UserInterface
     */
    
    /**
     * Identifiant unique de l'utilisateur (username dans notre cas)
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * Retourne les rôles de l'utilisateur
     * Par défaut, tout le monde a ROLE_USER
     */
    public function getRoles(): array
    {
        // Garantir que chaque utilisateur a au moins ROLE_USER
        return ['ROLE_USER'];
    }

    /**
     * Efface les données sensibles temporaires (comme le plainPassword)
     */
    public function eraseCredentials(): void
    {
        // Si vous stockez temporairement un plainPassword, effacez-le ici
        // $this->plainPassword = null;
    }
}
