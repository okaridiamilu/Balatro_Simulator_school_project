<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['user'];
    }

    public function load(ObjectManager $manager): void
    {
        // Créer un utilisateur de test
        $user = new User();
        $user->setUsername('player1');
        $user->setPassword('password123'); // En production, utiliser un hash !
        
        $manager->persist($user);

        // Créer un deuxième utilisateur
        $user2 = new User();
        $user2->setUsername('player2');
        $user2->setPassword('password123');
        
        $manager->persist($user2);

        $manager->flush();
    }
}
