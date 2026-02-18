<?php

namespace App\Tests\Entity;

use App\Entity\Partie;
use App\Entity\User;
use App\Entity\JokerInstance;
use App\Entity\JokerTemplate;
use App\Entity\HandLevel;
use App\Enum\EtatJoker;
use App\Enum\RareteJoker;
use App\Enum\TypeStack;
use PHPUnit\Framework\TestCase;

class PartieTest extends TestCase
{
    private function createPartie(): Partie
    {
        $user = new User();
        $user->setUsername('testuser');
        $user->setPassword('test');

        $handLevel = new HandLevel();
        $handLevel->setPair(0)
            ->setTwoPair(0)
            ->setThreeOfAKind(0)
            ->setStraight(0)
            ->setFlush(0)
            ->setFullHouse(0)
            ->setFourOfAKind(0)
            ->setStraightFlush(0)
            ->setRoyalFlush(0)
            ->setHighCard(0);

        $partie = new Partie();
        $partie->setUser($user);
        $partie->setIdentifiant('Test Partie');
        $partie->setMoney(50);
        $partie->setHand(8);
        $partie->setDiscard(3);
        $partie->setJokerSlots(5);
        $partie->setHandLevel($handLevel);

        return $partie;
    }

    private function createJokerTemplate(string $nom = 'Test Joker'): JokerTemplate
    {
        $template = new JokerTemplate();
        $template->setNom($nom);
        $template->setDescription('Test description');
        $template->setRarete(RareteJoker::COMMUN);
        $template->setTypeStack(TypeStack::NONE);
        $template->setStackParUnite(0);

        return $template;
    }

    public function testPartieInitialization(): void
    {
        $partie = $this->createPartie();

        $this->assertEquals('Test Partie', $partie->getIdentifiant());
        $this->assertEquals(50, $partie->getMoney());
        $this->assertEquals(8, $partie->getHand());
        $this->assertEquals(3, $partie->getDiscard());
        $this->assertEquals(5, $partie->getJokerSlots());
        $this->assertFalse($partie->isObservatoireActif());
    }

    public function testJokerSlotsCalculation(): void
    {
        $partie = $this->createPartie();

        // Au départ : 5 slots, 0 jokers utilisés
        $this->assertEquals(5, $partie->getTotalJokerSlots());
        $this->assertEquals(0, $partie->getUsedJokerSlots());
        $this->assertEquals(5, $partie->getAvailableJokerSlots());

        // Ajouter un joker normal
        $joker1 = new JokerInstance();
        $joker1->setJokerTemplate($this->createJokerTemplate('Joker 1'));
        $joker1->setPartie($partie);
        $joker1->setOrdre(1);
        $partie->addJoker($joker1);

        $this->assertEquals(5, $partie->getTotalJokerSlots());
        $this->assertEquals(1, $partie->getUsedJokerSlots());
        $this->assertEquals(4, $partie->getAvailableJokerSlots());
    }

    public function testNegativeJokerAddsSlot(): void
    {
        $partie = $this->createPartie();

        // Ajouter un joker négatif
        $jokerNegatif = new JokerInstance();
        $jokerNegatif->setJokerTemplate($this->createJokerTemplate('Joker Négatif'));
        $jokerNegatif->setPartie($partie);
        $jokerNegatif->setOrdre(1);
        $jokerNegatif->setEtat(EtatJoker::NEGATIF);
        $partie->addJoker($jokerNegatif);

        // Le joker négatif ajoute 1 slot mais en utilise 1 aussi
        // Total = 5 + 1 = 6, Utilisés = 1, Disponibles = 5
        $this->assertEquals(6, $partie->getTotalJokerSlots());
        $this->assertEquals(1, $partie->getUsedJokerSlots());
        $this->assertEquals(5, $partie->getAvailableJokerSlots());
    }

    public function testMultipleNegativeJokers(): void
    {
        $partie = $this->createPartie();

        // Ajouter 3 jokers négatifs
        for ($i = 1; $i <= 3; $i++) {
            $joker = new JokerInstance();
            $joker->setJokerTemplate($this->createJokerTemplate("Joker Négatif $i"));
            $joker->setPartie($partie);
            $joker->setOrdre($i);
            $joker->setEtat(EtatJoker::NEGATIF);
            $partie->addJoker($joker);
        }

        // Total = 5 + 3 = 8, Utilisés = 3, Disponibles = 5
        $this->assertEquals(8, $partie->getTotalJokerSlots());
        $this->assertEquals(3, $partie->getUsedJokerSlots());
        $this->assertEquals(5, $partie->getAvailableJokerSlots());
    }

    public function testMixedJokers(): void
    {
        $partie = $this->createPartie();

        // Ajouter 2 jokers normaux
        for ($i = 1; $i <= 2; $i++) {
            $joker = new JokerInstance();
            $joker->setJokerTemplate($this->createJokerTemplate("Joker Normal $i"));
            $joker->setPartie($partie);
            $joker->setOrdre($i);
            $partie->addJoker($joker);
        }

        // Ajouter 1 joker négatif
        $jokerNegatif = new JokerInstance();
        $jokerNegatif->setJokerTemplate($this->createJokerTemplate('Joker Négatif'));
        $jokerNegatif->setPartie($partie);
        $jokerNegatif->setOrdre(3);
        $jokerNegatif->setEtat(EtatJoker::NEGATIF);
        $partie->addJoker($jokerNegatif);

        // Total = 5 + 1 = 6, Utilisés = 3, Disponibles = 3
        $this->assertEquals(6, $partie->getTotalJokerSlots());
        $this->assertEquals(3, $partie->getUsedJokerSlots());
        $this->assertEquals(3, $partie->getAvailableJokerSlots());
    }

    public function testObservatoireToggle(): void
    {
        $partie = $this->createPartie();

        $this->assertFalse($partie->isObservatoireActif());

        $partie->setObservatoireActif(true);
        $this->assertTrue($partie->isObservatoireActif());

        $partie->setObservatoireActif(false);
        $this->assertFalse($partie->isObservatoireActif());
    }

    public function testJokerRemoval(): void
    {
        $partie = $this->createPartie();

        // Ajouter 3 jokers
        $jokers = [];
        for ($i = 1; $i <= 3; $i++) {
            $joker = new JokerInstance();
            $joker->setJokerTemplate($this->createJokerTemplate("Joker $i"));
            $joker->setPartie($partie);
            $joker->setOrdre($i);
            $partie->addJoker($joker);
            $jokers[] = $joker;
        }

        $this->assertEquals(3, $partie->getUsedJokerSlots());

        // Retirer un joker
        $partie->removeJoker($jokers[1]);

        $this->assertEquals(2, $partie->getUsedJokerSlots());
        $this->assertEquals(3, $partie->getAvailableJokerSlots());
    }

    public function testCartesCollection(): void
    {
        $partie = $this->createPartie();

        $this->assertCount(0, $partie->getCartes());

        // Note: On ne peut pas créer facilement des Carte ici sans Doctrine
        // Ce test vérifie juste que la collection est initialisée
        $this->assertNotNull($partie->getCartes());
    }

    public function testConsommablesCollection(): void
    {
        $partie = $this->createPartie();

        $this->assertCount(0, $partie->getConsommables());
        $this->assertNotNull($partie->getConsommables());
    }
}
