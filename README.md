README de mon projet de Symfony  🐬 🌈 ✨

Bonjour, voici mon projet de Symfony. (ne fais pas attention au nom du dossier, ceci n'est pas la correction du TP2, je vous rassure.)
Mon projet consistait à faire un simulateur Balatro, une sorte de "what if" qui vous permet de reconstituer des situations précises pour les calculer. Une certaine quantité d'argent, de jokers, de cartes, consommables...

## 📦 Installation

### Prérequis
- PHP 8.1 ou supérieur
- Composer
- MySQL/MariaDB
- Symfony CLI (optionnel mais recommandé)

### Étapes d'installation

1. **Cloner le projet**
```bash
git clone https://github.com/okaridiamilu/Balatro_Simulator_school_project.git
cd TP2-correction
```

2. **Installer les dépendances**
```bash
composer install
```

3. **Configurer la base de données**
Créer un fichier `.env.local` (si inexistant) et configurer votre connexion MySQL :
```env
DATABASE_URL="mysql://votre_user:votre_password@127.0.0.1:3306/balatro_db?serverVersion=8.0"
```

4. **Créer la base de données**
```bash
php bin/console doctrine:database:create
```

5. **Exécuter les migrations**
```bash
php bin/console doctrine:migrations:migrate
```

6. **Importer les jokers templates (IMPORTANT)**
Les jokers templates sont essentiels pour le fonctionnement du dictionnaire et pour ajouter des jokers aux parties.
```bash
mysql -u votre_user -p balatro_db < joker_templates.sql
```
Ou si vous utilisez un client MySQL graphique (phpMyAdmin, MySQL Workbench), importez simplement le fichier `joker_templates.sql`.

7. **Lancer le serveur**
```bash
symfony serve
# OU
php -S localhost:8000 -t public
```

8. **Accéder à l'application**
Ouvrez votre navigateur à l'adresse : `http://localhost:8000`

### Première utilisation
- Créez un compte utilisateur via la page d'inscription
- Connectez-vous
- Créez votre première partie !
- Explorez le dictionnaire des jokers pour voir tous les jokers disponibles

---

Comment ça marche?
    Il suffit de se faire un compte. En vous connectant vous serez directement emmené à la page "Mes parties". Vous pourrez donc créer votre propre partie de toute pièce OU en importer une sous format JSON (ou la faire vous-même en format JSON à la main, mais à ce niveau-là à quoi ça sert de faire une interface graphique?)

Qu'est-ce que je fais avec une partie?
    Créez vos jokers, personnalisez-les. Recréez vos conditions de test, avec vos tailles de main, votre argent, votre deck personnalisable (de A à Z), vos consommables et MÊME les stacks des scaling jokers!! Le site essaie de reprendre presque toutes les informations nécessaires pour le scoring (le reste a été mis de côté pour rester simple)

Comment partager ou recevoir un profil?
    Vous avez un bouton exporter en JSON sur chacune de vos parties, et un bouton importer en JSON dans la page partie! (Je n'ai pas mis de feature drag and drop, il faut obligatoirement passer par l'interface)

Que puis-je faire d'autre?
    Vous pouvez également regarder et examiner le dictionnaire des jokers! Afin d'avoir un aperçu global de ces derniers.

Niveau code, comment ça s'organise.
Nous avons 
    8 Entités
        les cartes
        les consommables
        les niveaux de main
        les instances de jokers (les jokers qu'on va retrouver dans la partie du joueur)
        les jokers template (les jokers modèles de référence, "les classiques" qu'on peut retrouver dans le dictionnaire.)
        les parties des joueurs
        les utilisateurs
        les vouchers (je garde l'entité car en soi l'idée est là, mais j'ai une alternative beaucoup plus simple, donc j'ai fini par ne pas l'utiliser mais je le garde pour montrer la réflexion que j'ai eue)
    
    3 Forms
        JokerFilterType (qui est le filtre pour le dictionnaire).
        JokerInstanceType (qui sert à ajouter des jokers à une partie, utilisant les jokers templates comme référence)
        JokerTemplateType (qui... est l'ancienne version de comment je comptais gérer mes jokers, mais c'était un enfer, trop de problèmes notamment au niveau algo sur les maths (que je n'ai jamais faites). Je le garde car c'est le premier form que j'ai fait, ET il pourrait servir pour un système de joker personnalisé à 100%, mais ça demanderait beaucoup de travail donc j'ai préféré contourner le problème. (encore une fois je ne suis pas game dev))

    18 templates Twig (après nettoyage)
        bundle error pack: 
            dossier qui contient toutes mes pages d'erreurs (403, 404, 500) (qui marche qu'en mode prod, je me suis déjà fait avoir)
        carte/deck (la page pour ajouter des cartes dans le deck)
        consommable/manage (la page pour ajouter des consommables dans la partie)
        dictionary/dictionary (la page de dictionnaire des jokers)
        hand_level/levels (la page pour gérer les niveaux de mains jouées)
        joker (le fichier est un vestige de l'ancien formulaire pour faire les jokers, je trouvais ça intéressant de le garder pour garder des traces de l'évolution. On peut aussi le supprimer, ça ne cassera pas le code.)
        partials/_menu (la nav bar qu'on va retrouver partout et qui est réutilisée partout)
        partie: 
            import (qui va servir à l'import de fichier JSON pour enregistrer des parties faites par des tiers.)
            index (page qui va montrer toutes les parties du joueur.)
            show (la page qui va montrer le contenu d'une partie, jokers, main, consommables, etc...)
        security:
            home: (page d'accueil principale)
            login
            register
        alert (pour les alertes pour toutes les pages)
        base (et la référence de base pour toutes les pages du site)
    
    1 test
        test de génération de cartes!


Les technos utilisées:
    Symfony 7 (framework PHP)
    Doctrine ORM (gestion de la base de données et des entités)
    Twig (moteur de templates)
    PHP 8 (avec les Enums natifs)
    MySQL
    HTML5 & CSS3
    Bootstrap 5
    JavaScript/AJAX (autocomplétion des jokers)
    JSON
    Composer
    CSRF Protection (cross-site request forgery - sécurité pour éviter que des attaques externes puissent faire des requêtes à notre place)
    MIME Type Validation (pour vérifier la nature du fichier JSON lors de l'import pour éviter tout malware)


Critère d'évaluations:
    dépôt git : je vous l'ai envoyé de mémoire. Si vous ne l'avez pas, le voici (https://github.com/okaridiamilu/Balatro_Simulator_school_project)
    je n'ai pas hébergé le projet malheureusement (d'ailleurs, faut faire attention aux ports que vous utilisez, ma config niveau port localhost est assez étrange. Je vous préviens.)
    cruds:
        1- parties
        2- Cartes
        3- Consommables
        4- JokerInstance (son update est un peu spécial mais on peut update des jokers (leur position par exemple) ou leur stack)
    système d'upload:
        J'ai upload et download! Les deux!
    
    fonctionnalité de tri:
        vous pourrez le voir dans le dictionnaire des jokers, vous pouvez trier par nom et rareté.

    recherche/filtres:
        idem, dans le dictionnaire, vous pouvez filtrer par nom (regex) et par rareté.

    authentification sécurisée : 
        j'ai crypté les passwords, et j'ai fait en sorte à ce qu'un utilisateur, même en jouant avec l'url ne puisse pas accéder aux parties des autres. Protection contre les injections SQL, j'ai protégé mes downloads avec 3 barrières (vérification d'extensions, MIME, et après vérifier l'intégrité/la forme du fichier avant d'essayer de créer l'entité)

    fonctionnalité supplémentaire:
        Le jeu n'est pas implémenté, beaucoup de concepts y sont pour le faire marcher, mais je n'ai pas eu assez de temps pour le faire. Ou j'ai un peu surestimé mes capacités. Néanmoins j'ai le upload et le download de mes fichiers JSON pour le partage inter utilisateur. J'ai également fait en sorte à ce qu'on puisse appliquer des modifications à plusieurs éléments en même temps, les supprimer ou les dupliquer.
    
    Responsive:
        C'est responsive avec menu burger!

    bugs:
        y en a pas car je suis trop fort (JE RIGOLE - sur une note sérieuse je n'en ai pas vu directement. Mais ça ne veut pas dire qu'il n'y en a pas. En tout cas, pas de bugs qui me font une erreur 500 ou 404)

    