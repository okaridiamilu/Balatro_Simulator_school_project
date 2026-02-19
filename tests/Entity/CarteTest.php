<?php

namespace App\Tests\Entity;

use App\Entity\Partie;
use App\Entity\Carte;
use App\Enum\CarteNumber;
use App\Enum\CarteColor;
use App\Enum\CarteStatus;
use App\Enum\CarteStatusSeal;
use App\Enum\CarteStatusMatter;
use PHPUnit\Framework\TestCase;

class CarteTest extends TestCase
{
    /**
     * Test qu'un deck standard contient 52 cartes
     */
    public function testStandardDeckHas52Cards(): void
    {
        $partie = new Partie();
        $partie->setIdentifiant('Test Deck');
        
        // Créer un deck standard complet
        $cards = [];
        foreach (CarteColor::cases() as $color) {
            foreach (CarteNumber::cases() as $number) {
                $carte = new Carte();
                $carte->setNumber($number);
                $carte->setColor($color);
                $carte->setStatus(CarteStatus::BASE);
                $carte->setSeal(CarteStatusSeal::BASE);
                $carte->setMatter(CarteStatusMatter::BASE);
                $partie->addCarte($carte);
                $cards[] = $carte;
            }
        }
        
        $this->assertCount(52, $partie->getCartes());
    }

    /**
     * Test que chaque couleur a 13 cartes
     */
    public function testEachColorHas13Cards(): void
    {
        $partie = new Partie();
        $partie->setIdentifiant('Test Colors');
        
        // Créer un deck standard
        foreach (CarteColor::cases() as $color) {
            foreach (CarteNumber::cases() as $number) {
                $carte = new Carte();
                $carte->setNumber($number);
                $carte->setColor($color);
                $carte->setStatus(CarteStatus::BASE);
                $carte->setSeal(CarteStatusSeal::BASE);
                $carte->setMatter(CarteStatusMatter::BASE);
                $partie->addCarte($carte);
            }
        }
        
        // Vérifier que chaque couleur a exactement 13 cartes
        foreach (CarteColor::cases() as $color) {
            $cardsOfColor = array_filter(
                $partie->getCartes()->toArray(),
                fn($carte) => $carte->getColor() === $color
            );
            $this->assertCount(13, $cardsOfColor, "La couleur {$color->value} devrait avoir 13 cartes");
        }
    }

    /**
     * Test l'ajout et la suppression de cartes
     */
    public function testAddAndRemoveCards(): void
    {
        $partie = new Partie();
        $partie->setIdentifiant('Test Add/Remove');
        
        // Ajouter une carte
        $carte1 = new Carte();
        $carte1->setNumber(CarteNumber::ACE);
        $carte1->setColor(CarteColor::SPADE);
        $partie->addCarte($carte1);
        
        $this->assertCount(1, $partie->getCartes());
        $this->assertTrue($partie->getCartes()->contains($carte1));
        
        // Ajouter une deuxième carte
        $carte2 = new Carte();
        $carte2->setNumber(CarteNumber::KING);
        $carte2->setColor(CarteColor::HEARTS);
        $partie->addCarte($carte2);
        
        $this->assertCount(2, $partie->getCartes());
        
        // Retirer la première carte
        $partie->removeCarte($carte1);
        
        $this->assertCount(1, $partie->getCartes());
        $this->assertFalse($partie->getCartes()->contains($carte1));
        $this->assertTrue($partie->getCartes()->contains($carte2));
    }

    /**
     * Test la modification du statut d'une carte
     */
    public function testCardStatusModification(): void
    {
        $carte = new Carte();
        $carte->setNumber(CarteNumber::QUEEN);
        $carte->setColor(CarteColor::DIAMONDS);
        $carte->setStatus(CarteStatus::BASE);
        
        $this->assertSame(CarteStatus::BASE, $carte->getStatus());
        
        // Changer en Foil
        $carte->setStatus(CarteStatus::FOIL);
        $this->assertSame(CarteStatus::FOIL, $carte->getStatus());
        
        // Changer en Holographic
        $carte->setStatus(CarteStatus::HOLOGRAPHIC);
        $this->assertSame(CarteStatus::HOLOGRAPHIC, $carte->getStatus());
        
        // Changer en Polychrome
        $carte->setStatus(CarteStatus::POLYCHROME);
        $this->assertSame(CarteStatus::POLYCHROME, $carte->getStatus());
    }

    /**
     * Test la modification du sceau d'une carte
     */
    public function testCardSealModification(): void
    {
        $carte = new Carte();
        $carte->setNumber(CarteNumber::JACK);
        $carte->setColor(CarteColor::CLUBS);
        $carte->setSeal(CarteStatusSeal::BASE);
        
        $this->assertSame(CarteStatusSeal::BASE, $carte->getSeal());
        
        // Changer en sceau or
        $carte->setSeal(CarteStatusSeal::GOLD);
        $this->assertSame(CarteStatusSeal::GOLD, $carte->getSeal());
        
        // Changer en sceau rouge
        $carte->setSeal(CarteStatusSeal::RED);
        $this->assertSame(CarteStatusSeal::RED, $carte->getSeal());
    }

    /**
     * Test la modification de l'amélioration d'une carte
     */
    public function testCardMatterModification(): void
    {
        $carte = new Carte();
        $carte->setNumber(CarteNumber::TEN);
        $carte->setColor(CarteColor::HEARTS);
        $carte->setMatter(CarteStatusMatter::BASE);
        
        $this->assertSame(CarteStatusMatter::BASE, $carte->getMatter());
        
        // Changer en Bonus
        $carte->setMatter(CarteStatusMatter::BONUS);
        $this->assertSame(CarteStatusMatter::BONUS, $carte->getMatter());
        
        // Changer en Mult
        $carte->setMatter(CarteStatusMatter::MULT);
        $this->assertSame(CarteStatusMatter::MULT, $carte->getMatter());
        
        // Changer en Glass
        $carte->setMatter(CarteStatusMatter::GLASS);
        $this->assertSame(CarteStatusMatter::GLASS, $carte->getMatter());
    }

    /**
     * Test que toutes les valeurs de cartes sont présentes
     */
    public function testAllCardNumbersExist(): void
    {
        $expectedNumbers = [
            CarteNumber::ACE,
            CarteNumber::TWO,
            CarteNumber::THREE,
            CarteNumber::FOUR,
            CarteNumber::FIVE,
            CarteNumber::SIX,
            CarteNumber::SEVEN,
            CarteNumber::EIGHT,
            CarteNumber::NINE,
            CarteNumber::TEN,
            CarteNumber::JACK,
            CarteNumber::QUEEN,
            CarteNumber::KING,
        ];
        
        $this->assertCount(13, CarteNumber::cases());
        
        foreach ($expectedNumbers as $number) {
            $this->assertContains($number, CarteNumber::cases());
        }
    }

    /**
     * Test que toutes les couleurs de cartes existent
     */
    public function testAllCardColorsExist(): void
    {
        $expectedColors = [
            CarteColor::SPADE,
            CarteColor::HEARTS,
            CarteColor::CLUBS,
            CarteColor::DIAMONDS,
        ];
        
        $this->assertCount(4, CarteColor::cases());
        
        foreach ($expectedColors as $color) {
            $this->assertContains($color, CarteColor::cases());
        }
    }

    /**
     * Test la réinitialisation d'une carte à son état de base
     */
    public function testResetCardToBase(): void
    {
        $carte = new Carte();
        $carte->setNumber(CarteNumber::SEVEN);
        $carte->setColor(CarteColor::SPADE);
        
        // Modifier tous les attributs
        $carte->setStatus(CarteStatus::POLYCHROME);
        $carte->setSeal(CarteStatusSeal::PURPLE);
        $carte->setMatter(CarteStatusMatter::GOLD);
        
        $this->assertNotSame(CarteStatus::BASE, $carte->getStatus());
        $this->assertNotSame(CarteStatusSeal::BASE, $carte->getSeal());
        $this->assertNotSame(CarteStatusMatter::BASE, $carte->getMatter());
        
        // Réinitialiser à l'état de base
        $carte->setStatus(CarteStatus::BASE);
        $carte->setSeal(CarteStatusSeal::BASE);
        $carte->setMatter(CarteStatusMatter::BASE);
        
        $this->assertSame(CarteStatus::BASE, $carte->getStatus());
        $this->assertSame(CarteStatusSeal::BASE, $carte->getSeal());
        $this->assertSame(CarteStatusMatter::BASE, $carte->getMatter());
    }

    /**
     * Test la combinaison de plusieurs améliorations sur une carte
     */
    public function testCardWithMultipleEnhancements(): void
    {
        $carte = new Carte();
        $carte->setNumber(CarteNumber::ACE);
        $carte->setColor(CarteColor::HEARTS);
        $carte->setStatus(CarteStatus::FOIL);
        $carte->setSeal(CarteStatusSeal::GOLD);
        $carte->setMatter(CarteStatusMatter::STEEL);
        
        // Vérifier que tous les attributs sont correctement définis
        $this->assertSame(CarteNumber::ACE, $carte->getNumber());
        $this->assertSame(CarteColor::HEARTS, $carte->getColor());
        $this->assertSame(CarteStatus::FOIL, $carte->getStatus());
        $this->assertSame(CarteStatusSeal::GOLD, $carte->getSeal());
        $this->assertSame(CarteStatusMatter::STEEL, $carte->getMatter());
    }
}
