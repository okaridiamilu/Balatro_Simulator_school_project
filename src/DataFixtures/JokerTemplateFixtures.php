<?php

namespace App\DataFixtures;

use App\Entity\JokerTemplate;
use App\Enum\RareteJoker;
use App\Enum\TypeStack;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class JokerTemplateFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // 1. VAMPIRE - Gagne X Mult par carte Enhanced jouée
        $vampire = new JokerTemplate();
        $vampire->setNom('Vampire')
            ->setRarete(RareteJoker::UNCOMMUN)
            ->setDescription('Gagne x0.5 Mult par carte Enhanced jouée. Se réinitialise à la fin du round.')
            ->setImage('/images/jokers/vampire.png')
            ->setEffetCode('vampire')
            ->setConditionActivation(['trigger' => 'on_card_played', 'card_type' => 'enhanced'])
            ->setTypeStack(TypeStack::XMULT)
            ->setStackParUnite(0.5);
        $manager->persist($vampire);

        // 2. BARON - Mult si main contient un Roi
        $baron = new JokerTemplate();
        $baron->setNom('Baron')
            ->setRarete(RareteJoker::RARE)
            ->setDescription('Chaque Roi en main donne x1.5 Mult')
            ->setImage('/images/jokers/baron.png')
            ->setEffetCode('baron')
            ->setConditionActivation(['trigger' => 'card_held', 'card_rank' => 'King'])
            ->setTypeStack(TypeStack::XMULT)
            ->setStackParUnite(1.5);
        $manager->persist($baron);

        // 3. JOKER - Simple bonus de chips
        $joker = new JokerTemplate();
        $joker->setNom('Joker')
            ->setRarete(RareteJoker::COMMUN)
            ->setDescription('+4 Mult')
            ->setImage('/images/jokers/joker.png')
            ->setEffetCode('simple_mult')
            ->setConditionActivation(['trigger' => 'always'])
            ->setTypeStack(TypeStack::MULT_FLAT)
            ->setStackParUnite(4);
        $manager->persist($joker);

        // 4. GREEDY JOKER - Gagne chips quand on joue des cartes Diamond
        $greedy = new JokerTemplate();
        $greedy->setNom('Greedy Joker')
            ->setRarete(RareteJoker::COMMUN)
            ->setDescription('Les cartes jouées avec la couleur Diamond donnent +3 Mult lorsqu\'elles sont scorées')
            ->setImage('/images/jokers/greedy.png')
            ->setEffetCode('greedy_joker')
            ->setConditionActivation(['trigger' => 'on_card_scored', 'suit' => 'Diamond'])
            ->setTypeStack(TypeStack::MULT_FLAT)
            ->setStackParUnite(3);
        $manager->persist($greedy);

        // 5. LUSTY JOKER - Bonus pour cartes Heart
        $lusty = new JokerTemplate();
        $lusty->setNom('Lusty Joker')
            ->setRarete(RareteJoker::COMMUN)
            ->setDescription('Les cartes jouées avec la couleur Heart donnent +3 Mult lorsqu\'elles sont scorées')
            ->setImage('/images/jokers/lusty.png')
            ->setEffetCode('lusty_joker')
            ->setConditionActivation(['trigger' => 'on_card_scored', 'suit' => 'Heart'])
            ->setTypeStack(TypeStack::MULT_FLAT)
            ->setStackParUnite(3);
        $manager->persist($lusty);

        // 6. WRATHFUL JOKER - Bonus pour cartes Spade
        $wrathful = new JokerTemplate();
        $wrathful->setNom('Wrathful Joker')
            ->setRarete(RareteJoker::COMMUN)
            ->setDescription('Les cartes jouées avec la couleur Spade donnent +3 Mult lorsqu\'elles sont scorées')
            ->setImage('/images/jokers/wrathful.png')
            ->setEffetCode('wrathful_joker')
            ->setConditionActivation(['trigger' => 'on_card_scored', 'suit' => 'Spade'])
            ->setTypeStack(TypeStack::MULT_FLAT)
            ->setStackParUnite(3);
        $manager->persist($wrathful);

        // 7. GLUTTONOUS JOKER - Bonus pour cartes Club
        $gluttonous = new JokerTemplate();
        $gluttonous->setNom('Gluttonous Joker')
            ->setRarete(RareteJoker::COMMUN)
            ->setDescription('Les cartes jouées avec la couleur Club donnent +3 Mult lorsqu\'elles sont scorées')
            ->setImage('/images/jokers/gluttonous.png')
            ->setEffetCode('gluttonous_joker')
            ->setConditionActivation(['trigger' => 'on_card_scored', 'suit' => 'Club'])
            ->setTypeStack(TypeStack::MULT_FLAT)
            ->setStackParUnite(3);
        $manager->persist($gluttonous);

        // 8. SCARY FACE - Bonus quand on joue des figures
        $scaryFace = new JokerTemplate();
        $scaryFace->setNom('Scary Face')
            ->setRarete(RareteJoker::COMMUN)
            ->setDescription('Les cartes Figures jouées donnent +30 Chips lorsqu\'elles sont scorées')
            ->setImage('/images/jokers/scary_face.png')
            ->setEffetCode('scary_face')
            ->setConditionActivation(['trigger' => 'on_card_scored', 'card_type' => 'face'])
            ->setTypeStack(TypeStack::CHIPS)
            ->setStackParUnite(30);
        $manager->persist($scaryFace);

        // 9. ABSTRACT JOKER - Bonus par Joker possédé
        $abstract = new JokerTemplate();
        $abstract->setNom('Abstract Joker')
            ->setRarete(RareteJoker::COMMUN)
            ->setDescription('+3 Mult par Joker possédé')
            ->setImage('/images/jokers/abstract.png')
            ->setEffetCode('abstract_joker')
            ->setConditionActivation(['trigger' => 'per_joker'])
            ->setTypeStack(TypeStack::MULT_FLAT)
            ->setStackParUnite(3);
        $manager->persist($abstract);

        // 10. MYSTIC SUMMIT - Bonus quand reste 1 défausse
        $mysticSummit = new JokerTemplate();
        $mysticSummit->setNom('Mystic Summit')
            ->setRarete(RareteJoker::COMMUN)
            ->setDescription('+15 Mult si 0 défausses restantes')
            ->setImage('/images/jokers/mystic_summit.png')
            ->setEffetCode('mystic_summit')
            ->setConditionActivation(['trigger' => 'discards_remaining', 'value' => 0])
            ->setTypeStack(TypeStack::MULT_FLAT)
            ->setStackParUnite(15);
        $manager->persist($mysticSummit);

        // 11. FIBONACCI - Bonus pour A, 2, 3, 5, 8
        $fibonacci = new JokerTemplate();
        $fibonacci->setNom('Fibonacci')
            ->setRarete(RareteJoker::UNCOMMUN)
            ->setDescription('Chaque As, 2, 3, 5 ou 8 joué donne +8 Mult lorsqu\'il est scoré')
            ->setImage('/images/jokers/fibonacci.png')
            ->setEffetCode('fibonacci')
            ->setConditionActivation(['trigger' => 'on_card_scored', 'ranks' => ['A', '2', '3', '5', '8']])
            ->setTypeStack(TypeStack::MULT_FLAT)
            ->setStackParUnite(8);
        $manager->persist($fibonacci);

        // 12. LOYALTY CARD - Bonus qui augmente tous les 6 mains
        $loyaltyCard = new JokerTemplate();
        $loyaltyCard->setNom('Loyalty Card')
            ->setRarete(RareteJoker::UNCOMMUN)
            ->setDescription('x1.25 Mult tous les 6 mains jouées. Augmente de x0.25 tous les 6 mains.')
            ->setImage('/images/jokers/loyalty_card.png')
            ->setEffetCode('loyalty_card')
            ->setConditionActivation(['trigger' => 'every_n_hands', 'interval' => 6])
            ->setTypeStack(TypeStack::XMULT)
            ->setStackParUnite(0.25);
        $manager->persist($loyaltyCard);

        // 13. CONSTELLATION - Bonus par carte Planet utilisée
        $constellation = new JokerTemplate();
        $constellation->setNom('Constellation')
            ->setRarete(RareteJoker::UNCOMMUN)
            ->setDescription('Gagne x0.1 Mult par carte Planet utilisée')
            ->setImage('/images/jokers/constellation.png')
            ->setEffetCode('constellation')
            ->setConditionActivation(['trigger' => 'on_planet_card_used'])
            ->setTypeStack(TypeStack::XMULT)
            ->setStackParUnite(0.1);
        $manager->persist($constellation);

        // 14. HIKER - Bonus permanent par main jouée
        $hiker = new JokerTemplate();
        $hiker->setNom('Hiker')
            ->setRarete(RareteJoker::UNCOMMUN)
            ->setDescription('Chaque main jouée donne définitivement +4 Chips')
            ->setImage('/images/jokers/hiker.png')
            ->setEffetCode('hiker')
            ->setConditionActivation(['trigger' => 'on_hand_played'])
            ->setTypeStack(TypeStack::CHIPS)
            ->setStackParUnite(4);
        $manager->persist($hiker);

        // 15. SQUARE JOKER - Bonus si exactement 4 cartes dans la main jouée
        $square = new JokerTemplate();
        $square->setNom('Square Joker')
            ->setRarete(RareteJoker::COMMUN)
            ->setDescription('+16 Chips si la main jouée contient exactement 4 cartes')
            ->setImage('/images/jokers/square.png')
            ->setEffetCode('square_joker')
            ->setConditionActivation(['trigger' => 'hand_size', 'value' => 4])
            ->setTypeStack(TypeStack::CHIPS)
            ->setStackParUnite(16);
        $manager->persist($square);

        // 16. RAISED FIST - Bonus pour cartes de rang le plus bas
        $raisedFist = new JokerTemplate();
        $raisedFist->setNom('Raised Fist')
            ->setRarete(RareteJoker::COMMUN)
            ->setDescription('Ajoute le rang de la carte de rang le plus bas en main aux Mult (doublé si la carte a une amélioration)')
            ->setImage('/images/jokers/raised_fist.png')
            ->setEffetCode('raised_fist')
            ->setConditionActivation(['trigger' => 'lowest_rank_in_hand'])
            ->setTypeStack(TypeStack::MULT_FLAT)
            ->setStackParUnite(15);
        $manager->persist($raisedFist);

        // 17. CHAOS THE CLOWN - Gratuit
        $chaos = new JokerTemplate();
        $chaos->setNom('Chaos the Clown')
            ->setRarete(RareteJoker::COMMUN)
            ->setDescription('1 reroll gratuit par shop')
            ->setImage('/images/jokers/chaos.png')
            ->setEffetCode('chaos_clown')
            ->setConditionActivation(['trigger' => 'shop_reroll'])
            ->setTypeStack(TypeStack::MULT_FLAT)
            ->setStackParUnite(0);
        $manager->persist($chaos);

        // 18. BLUE JOKER - Gagne chips par cartes dans le deck
        $blue = new JokerTemplate();
        $blue->setNom('Blue Joker')
            ->setRarete(RareteJoker::COMMUN)
            ->setDescription('+2 Chips par carte restante dans le deck')
            ->setImage('/images/jokers/blue.png')
            ->setEffetCode('blue_joker')
            ->setConditionActivation(['trigger' => 'per_card_in_deck'])
            ->setTypeStack(TypeStack::CHIPS)
            ->setStackParUnite(2);
        $manager->persist($blue);

        // 19. SUPERNOVA - Ajoute le niveau de la main de poker jouée
        $supernova = new JokerTemplate();
        $supernova->setNom('Supernova')
            ->setRarete(RareteJoker::COMMUN)
            ->setDescription('Ajoute le nombre de fois que la main de poker a été jouée à Mult')
            ->setImage('/images/jokers/supernova.png')
            ->setEffetCode('supernova')
            ->setConditionActivation(['trigger' => 'poker_hand_level'])
            ->setTypeStack(TypeStack::MULT_FLAT)
            ->setStackParUnite(1);
        $manager->persist($supernova);

        // 20. RIDE THE BUS - Bonus qui augmente puis se réinitialise
        $rideBus = new JokerTemplate();
        $rideBus->setNom('Ride the Bus')
            ->setRarete(RareteJoker::COMMUN)
            ->setDescription('+1 Mult par main jouée consécutive sans cartes Figure. Se réinitialise si vous jouez une Figure.')
            ->setImage('/images/jokers/ride_bus.png')
            ->setEffetCode('ride_the_bus')
            ->setConditionActivation(['trigger' => 'consecutive_no_face'])
            ->setTypeStack(TypeStack::MULT_FLAT)
            ->setStackParUnite(1);
        $manager->persist($rideBus);

        // 21. SPACE JOKER - Probabilité de level up une main de poker
        $space = new JokerTemplate();
        $space->setNom('Space Joker')
            ->setRarete(RareteJoker::UNCOMMUN)
            ->setDescription('1 chance sur 4 d\'améliorer le niveau de la main de poker jouée')
            ->setImage('/images/jokers/space.png')
            ->setEffetCode('space_joker')
            ->setConditionActivation(['trigger' => 'random', 'probability' => 0.25])
            ->setTypeStack(TypeStack::MULT_FLAT)
            ->setStackParUnite(0);
        $manager->persist($space);

        // 22. EGG - Gains de valeur de vente à la fin du round
        $egg = new JokerTemplate();
        $egg->setNom('Egg')
            ->setRarete(RareteJoker::COMMUN)
            ->setDescription('Gagne 3$ de valeur de vente à la fin du round')
            ->setImage('/images/jokers/egg.png')
            ->setEffetCode('egg')
            ->setConditionActivation(['trigger' => 'end_of_round'])
            ->setTypeStack(TypeStack::MULT_FLAT)
            ->setStackParUnite(3);
        $manager->persist($egg);

        // 23. BOOTSTRAPS - Bonus par $ possédés
        $bootstraps = new JokerTemplate();
        $bootstraps->setNom('Bootstraps')
            ->setRarete(RareteJoker::UNCOMMUN)
            ->setDescription('+2 Mult par tranche de 5$ que vous possédez (maximum $50)')
            ->setImage('/images/jokers/bootstraps.png')
            ->setEffetCode('bootstraps')
            ->setConditionActivation(['trigger' => 'per_5_dollars', 'max' => 50])
            ->setTypeStack(TypeStack::MULT_FLAT)
            ->setStackParUnite(2);
        $manager->persist($bootstraps);

        // 24. STUNTMAN - Bonus chips et main size
        $stuntman = new JokerTemplate();
        $stuntman->setNom('Stuntman')
            ->setRarete(RareteJoker::UNCOMMUN)
            ->setDescription('+250 Chips, -1 taille de main')
            ->setImage('/images/jokers/stuntman.png')
            ->setEffetCode('stuntman')
            ->setConditionActivation(['trigger' => 'always'])
            ->setTypeStack(TypeStack::CHIPS)
            ->setStackParUnite(250);
        $manager->persist($stuntman);

        // 25. RUNNER - Bonus chips par carte Straight
        $runner = new JokerTemplate();
        $runner->setNom('Runner')
            ->setRarete(RareteJoker::COMMUN)
            ->setDescription('+15 Chips si la main contient une Straight')
            ->setImage('/images/jokers/runner.png')
            ->setEffetCode('runner')
            ->setConditionActivation(['trigger' => 'poker_hand', 'hand_type' => 'Straight'])
            ->setTypeStack(TypeStack::CHIPS)
            ->setStackParUnite(15);
        $manager->persist($runner);

        $manager->flush();
    }
}
