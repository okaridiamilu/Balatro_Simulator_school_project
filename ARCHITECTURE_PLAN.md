# 🎰 BALATRO SIMULATOR - Architecture & Planification

Date: 15 janvier 2026
Objectif: Simulateur de Balatro avec gestion de parties, cartes, jokers et consommables

---

## 📋 PROBLÉMATIQUES À RÉSOUDRE

### 1. Structure de la Base de Données
### 2. Logique de jeu (effets, calculs, mécaniques)
### 3. Gestion des jokers (templates vs instances)
### 4. Interface utilisateur et expérience

---

## 🗄️ ARCHITECTURE BDD - PROPOSITION

### A. TABLES PRINCIPALES

#### 👤 USER (Gestion des utilisateurs)
```
id: int PK AUTO_INCREMENT
username: string UNIQUE NOT NULL
password: string (hashé) NOT NULL
```
**Relations**: Un user a plusieurs parties (one-to-many)

---

#### 🎮 PARTIE (Session de jeu)
```
id: int PK AUTO_INCREMENT
user_id: int FK → USER(id)

# Mécaniques de jeu qui peuvent etre ajustable salon l'envie. avoir une valeur de défaut est bien si non précisé. mais l'utilisateur peut choisir comme bon le semble.

score: int DEFAULT 0
argent: int DEFAULT 4
mains_restantes: int DEFAULT 4
defausses_restantes: int DEFAULT 3
taille_main: int DEFAULT 8

# Objectifs, je pense qu'on va pas en mettre juste faire des scores sans objectifs, le but de l'outil c'est de tester.
# IMPORTANT il faudrait ajouter le nombre de main joué pour chaque main. car certains jokers scalent de cela. donc peut etre une petite liste ou je ne sais quoi qui serait updatable/modifiable par le joueur pour définire le nombre de fois que chaque main a été joué ainsi que le niveau de la main.  car apres le niveau de la main compte pour le calcule des points.
```
**Relations**: 
- Appartient à un User (many-to-one)
- A plusieurs cartes via table intermédiaire (many-to-many)
- A plusieurs jokers via table intermédiaire (many-to-many)
- A plusieurs consommables via table intermédiaire (many-to-many)

---

#### 🃏 JOKER_TEMPLATE (Jokers de référence - catalogue)
```
id: int PK AUTO_INCREMENT
nom: string UNIQUE NOT NULL
rarete: enum('commun', 'uncommon', 'rare', 'legendary')
description: string
effet_code: string (identifiant pour la logique PHP)
image: string (chemin fichier)
prix_base: int DEFAULT 0
condition_activation: string (JSON ou texte)

# NOUVEAU: Pour gérer les différents types de stack
type_stack: enum('aucun', 'chips', 'mult_flat', 'mult_multiplicateur', 'xmult') NULL
stack_par_unite: int NULL  (combien de bonus par stack, ex: 3 pour Vampire = +3 mult/cœur)
```
**Note**: Ce sont les MODÈLES de jokers (comme dans un catalogue)
Exemples: "Joker Vampire", "Baron", "Triboulet", etc.

**Exemples de configuration** :
```
# Vampire: +3 mult par cœur (mult flat)
nom: "Vampire"
type_stack: "mult_flat"
stack_par_unite: 3
condition_activation: {"type":"par_carte","condition":"couleur","valeur":"coeur"}

# Constellation: +1 mult par planète (compteur qui stack)
nom: "Constellation"
type_stack: "mult_flat"
stack_par_unite: 1
condition_activation: {"type":"par_main","condition":"toujours"}

# Baron: x1.5 mult si Roi (multiplicateur, pas de stack)
nom: "Baron"
type_stack: "mult_multiplicateur"
stack_par_unite: NULL
condition_activation: {"type":"par_main","condition":"contient_rang","valeur":"K"}

# Scary Face: +30 chips par figure (chips flat)
nom: "Scary Face"
type_stack: "chips"
stack_par_unite: 30
condition_activation: {"type":"par_carte","condition":"rang_in","valeurs":["J","Q","K"]}

# Red Card: +3 mult par paquet acheté (compteur, mult flat)
nom: "Red Card"
type_stack: "mult_flat"
stack_par_unite: 3
condition_activation: {"type":"par_main","condition":"toujours"}
```

---

#### 🎴 JOKER_INSTANCE (Jokers possédés dans une partie)
```
id: int PK AUTO_INCREMENT
partie_id: int FK → PARTIE(id)
joker_template_id: int FK → JOKER_TEMPLATE(id)
status: enum('normale', 'foil', 'polychrome', 'chromatique')
ordre: int (position dans le deck de jokers)
actif: boolean DEFAULT true

# NOUVEAU: Compteurs pour effets qui stackent
compteur_stack: int DEFAULT 0  (ex: nombre de cartes jouées, nombre de ventes, etc.)
donnees_supplementaires: JSON NULL  (pour effets complexes qui ont besoin de plusieurs valeurs)
```
**Relations**:
- Appartient à une partie
- Référence un joker_template
- Effet du joker défini par joker_template.effet_code

**Pourquoi séparer Template et Instance?**
✅ Template = définition du joker (réutilisable)
✅ Instance = joker spécifique dans UNE partie avec SON état (foil, position, etc.)
✅ Instance peut avoir des compteurs propres (stack de l'effet)

**Exemples de compteurs**:
- Joker "Constellation" : compteur_stack = nombre de planètes utilisées (chaque planète = +1 mult)
- Joker "Red Card" : compteur_stack = nombre de paquets bonus achetés
- Joker "Glass Joker" : compteur_stack = nombre de cartes Glass détruites

---

#### 🎴 CARTE (Cartes du deck - 52 cartes standard)
```
id: int PK AUTO_INCREMENT
valeur: enum('2','3','4','5','6','7','8','9','10','J','Q','K','A')
couleur: enum('coeur', 'carreau', 'trefle', 'pique')
image: string
```
**Note**: Table statique de 52 cartes (peut être pré-remplie)

---

#### 📦 CARTE_INSTANCE (Cartes dans une partie avec leurs améliorations)
```
id: int PK AUTO_INCREMENT
partie_id: int FK → PARTIE(id)
carte_id: int FK → CARTE(id)
amelioration: enum('aucune', 'bonus', 'mult', 'wild', 'glass', 'steel', 'stone', 'gold')
sceau: enum('aucun', 'rouge', 'bleu', 'or', 'violet')
edition: enum('aucune', 'foil', 'holographique', 'polychrome')
dans_main: boolean DEFAULT false (true si dans la main actuelle)
```
**Relations**:
- Appartient à une partie
- Référence une carte de base

---

#### 🧪 CONSOMABLE_TEMPLATE (Catalogue des consommables)
```
id: int PK AUTO_INCREMENT
nom: string UNIQUE NOT NULL
type: enum('tarot', 'planete', 'spectral', 'voucher')
description: string
effet_code: string (identifiant pour logique)
image: string
prix: int
```

---

#### 🧪 CONSOMABLE_INSTANCE (Consommables possédés)
```
id: int PK AUTO_INCREMENT
partie_id: int FK → PARTIE(id)
consomable_template_id: int FK → CONSOMABLE_TEMPLATE(id)
utilise: boolean DEFAULT false
```

---

#### 🎯 MAIN_JOUEE (Historique des mains jouées)
```
id: int PK AUTO_INCREMENT
partie_id: int FK → PARTIE(id)
type_main ENUM(  'high_card',  'pair',  'two_pair',  'three_of_a_kind',  'straight',  'flush',  'full_house',  'four_of_a_kind',  'straight_flush',  'royal_flush',  'five_of_a_kind',  'flush_house',  'flush_five')

score: int
jetons: int
mult: int
cartes_utilisees: JSON (array des carte_instance_ids)
jokers_actifs: JSON (array des joker_instance_ids)

**Pour les consomables, c'est assez important car UN voucher permet de faire en sorte a ce que les planette puisse, dans certaines situations, faire du mult.**

consomables_actifs: JSON (array des consomables_instance_ids)
timestamp: datetime
```
**Utilité**: Logs pour debug, stats, replay

---

## 🔗 TABLES INTERMÉDIAIRES (pour relations many-to-many)

Vous n'en avez PAS BESOIN avec l'approche INSTANCE ci-dessus !

**Pourquoi?**
- `JOKER_INSTANCE` a déjà `partie_id` → pas besoin de table intermédiaire
- `CARTE_INSTANCE` a déjà `partie_id` → pas besoin de table intermédiaire
- `CONSOMABLE_INSTANCE` a déjà `partie_id` → pas besoin de table intermédiaire

Les tables INSTANCE **SONT** déjà les tables intermédiaires améliorées !

---

## ⚙️ LOGIQUE DES EFFETS - Comment implémenter?

### 📌 EXPLICATION: effet_code et condition_activation

#### effet_code (string)
C'est l'**identifiant unique** de la classe PHP qui va calculer l'effet.

**Exemples**:
- `"vampire"` → classe `EffetVampire`
- `"baron"` → classe `EffetBaron`
- `"constellation"` → classe `EffetConstellation`
- `"card_sharp"` → classe `EffetCardSharp`

**Mapping dans le code**:
```php
// Service EffetJokerFactory
public function creerEffet(string $effectCode): EffetJoker {
    return match($effectCode) {
        'vampire' => new EffetVampire(),
        'baron' => new EffetBaron(),
        'constellation' => new EffetConstellation(),
        'card_sharp' => new EffetCardSharp(),
        // ... tous les autres jokers
        default => new EffetNul()  // Joker sans effet
    };
}
```

#### condition_activation (JSON string)
Décrit **quand** l'effet s'active. Stocké en JSON pour faciliter le parsing.

**Exemples**:
```json
// Joker Vampire: s'active sur chaque carte Cœur
{
  "type": "par_carte",
  "condition": "couleur",
  "valeur": "coeur"
}

// Baron: s'active sur chaque Roi
{
  "type": "par_carte",
  "condition": "rang",
  "valeur": "K"
}

// Constellation: s'active à chaque main jouée (effet global)
{
  "type": "par_main",
  "condition": "toujours"
}

// Card Sharp: s'active si la main contient une paire de Rois
{
  "type": "par_main",
  "condition": "contient_paire",
  "valeur": "K"
}

// Scary Face: s'active pour chaque carte figure (J, Q, K)
{
  "type": "par_carte",
  "condition": "rang_in",
  "valeurs": ["J", "Q", "K"]
}
```

**Utilisation dans le code**:
```php
class EffetVampire implements EffetJoker {
    public function calculer(Main $main, JokerInstance $joker, Partie $partie): ResultatEffet {
        $condition = json_decode($joker->getTemplate()->getConditionActivation(), true);
        
        if ($condition['type'] === 'par_carte') {
            $cartes = $main->getCartesParCouleur($condition['valeur']);
            $multBonus = count($cartes) * 3;  // +3 mult par cœur
            
            return new ResultatEffet(mult: $multBonus);
        }
        
        return new ResultatEffet();
    }
}
```

---

### 🎯 Gestion des effets qui STACKENT

Certains jokers accumulent des bonus au fil de la partie :
- **Constellation** : +1 mult par planète utilisée
- **Red Card** : +3 mult par paquet bonus acheté
- **Hologram** : +5 mult par main jouée
- **Square Joker** : +4 jetons par 4 cartes jouées

**Solution**: Utiliser `compteur_stack` dans `JOKER_INSTANCE`

#### Exemple: Constellation

**JOKER_TEMPLATE**:
```
nom: "Constellation"
effet_code: "constellation"
condition_activation: {"type":"par_main","condition":"toujours"}
```

**JOKER_INSTANCE** (dans une partie):
```
joker_template_id: 42 (Constellation)
compteur_stack: 7  (7 planètes utilisées)
```

**Classe PHP**:
```php
class EffetConstellation implements EffetJoker {
    public function calculer(Main $main, JokerInstance $joker, Partie $partie): ResultatEffet {
        // +1 mult par planète utilisée (compteur)
        $multBonus = $joker->getCompteurStack() * 1;
        
        return new ResultatEffet(mult: $multBonus);
    }
}
```

#### Interface utilisateur

L'utilisateur doit pouvoir **modifier manuellement** le compteur :

**Formulaire d'ajout/édition de joker** :
```twig
{{ form_row(jokerInstanceForm.joker_template) }}  {# Dropdown: choisir le joker #}
{{ form_row(jokerInstanceForm.status) }}          {# normale/foil/polychrome #}
{{ form_row(jokerInstanceForm.compteur_stack, {
    'label': 'Nombre de stacks (pour effets accumulables)',
    'attr': {'min': 0, 'placeholder': '0'}
}) }}
```

**Exemple concret** :
- Utilisateur ajoute "Constellation" à sa partie
- Définit `compteur_stack = 15` (car il a utilisé 15 planètes)
- Lors du calcul : Constellation donne +15 mult

---

### 📊 Table pour tracker les niveaux/usages des mains

Comme vous l'avez mentionné, certains jokers dépendent du **nombre de fois qu'une main a été jouée** ou de son **niveau**.

#### Nouvelle table: MAIN_STATS

```sql
CREATE TABLE main_stats (
    id INT PK AUTO_INCREMENT,
    partie_id INT FK → PARTIE(id),
    type_main ENUM('high_card', 'pair', 'two_pair', ..., 'flush_five'),
    niveau INT DEFAULT 1,
    nb_fois_jouee INT DEFAULT 0,
    jetons_base INT,  -- Jetons de base pour cette main à ce niveau
    mult_base INT,    -- Mult de base pour cette main à ce niveau
    
    UNIQUE(partie_id, type_main)
);
```

**Exemples de données**:
```
partie_id=1, type_main='pair', niveau=3, nb_fois_jouee=42, jetons_base=10, mult_base=2
partie_id=1, type_main='flush', niveau=5, nb_fois_jouee=8, jetons_base=35, mult_base=4
```

**Utilité** :
- Joker "Fortune Teller" : +1 mult par Tarot utilisé x (niveau de la main)
- Joker "Fibonacci" : jetons selon niveau de Quinte/Quinte Flush
- Tracking du nombre de fois qu'une main est jouée

**Interface** :
L'utilisateur peut modifier niveau et nb_fois_jouee pour simuler un état de partie avancé.

---

### 🎫 Simplification: Vouchers → Juste Observatory

**Bonne idée !** Pour commencer, on se concentre sur **Observatory** uniquement.

#### VOUCHER_INSTANCE (simplifié)

```sql
CREATE TABLE voucher_instance (
    id INT PK AUTO_INCREMENT,
    partie_id INT FK → PARTIE(id),
    nom VARCHAR(50) DEFAULT 'Observatory',
    actif BOOLEAN DEFAULT true
);
```

**Effet Observatory** :
- Les cartes "Planète" donnent aussi du MULT (et pas juste niveau de main)
- Implémentation simple dans le calcul

**Plus tard** : Si besoin, on peut ajouter d'autres vouchers (Overstock, Tarot Merchant, etc.)

---

### Option 1: Code PHP avec Strategy Pattern (CHOISI ✅)
```php
// Interface de base
interface EffetJoker {
    public function calculer(Main $main, JokerInstance $joker, Partie $partie): ResultatEffet;
}

// Classe de résultat
class ResultatEffet {
    public function __construct(
        public int $jetons = 0,
        public int $mult = 0,
        public float $multMultiplicateur = 1.0,
        public float $jetonsMultiplicateur = 1.0
    ) {}
}

// Exemple 1: Joker Vampire (+3 mult par cœur dans la main)
class EffetVampire implements EffetJoker {
    public function calculer(Main $main, JokerInstance $joker, Partie $partie): ResultatEffet {
        $nbCoeurs = count($main->getCartesParCouleur('coeur'));
        return new ResultatEffet(mult: $nbCoeurs * 3);
    }
}

// Exemple 2: Baron (x1.5 mult si main contient un Roi)
class EffetBaron implements EffetJoker {
    public function calculer(Main $main, JokerInstance $joker, Partie $partie): ResultatEffet {
        if ($main->contientRang('K')) {
            return new ResultatEffet(multMultiplicateur: 1.5);
        }
        return new ResultatEffet();
    }
}

// Exemple 3: Constellation (+1 mult par planète utilisée - avec stack)
class EffetConstellation implements EffetJoker {
    public function calculer(Main $main, JokerInstance $joker, Partie $partie): ResultatEffet {
        $stack = $joker->getCompteurStack();  // Nombre de planètes
        return new ResultatEffet(mult: $stack);
    }
}

// Exemple 4: Card Sharp (+100 jetons si main contient paire de Rois)
class EffetCardSharp implements EffetJoker {
    public function calculer(Main $main, JokerInstance $joker, Partie $partie): ResultatEffet {
        if ($main->contientPaireDe('K')) {
            return new ResultatEffet(jetons: 100);
        }
        return new ResultatEffet();
    }
}
```

### 🎯 RECOMMANDATION: Approche HYBRIDE (PHP + Config) ✅

**Problème** : 150 classes PHP = beaucoup de code redondant

**Solution** : Combiner les deux approches

---

## 🔍 COMMENT EffetGenerique gère 100 jokers différents ?

**Réponse courte** : La classe ne contient PAS la logique spécifique. Elle LIT la configuration de chaque joker depuis la BDD et applique une logique générique.

### Principe : Configuration-Driven

Chaque joker a sa **configuration unique** dans `JOKER_TEMPLATE` :
- `condition_activation` : QUAND l'effet s'active
- `type_stack` : QUEL type de bonus (chips/mult/multiplicateur)
- `stack_par_unite` : COMBIEN de bonus par activation

**`EffetGenerique` lit cette config et l'exécute** → Même code, résultats différents !

---

### 🎯 EXEMPLES CONCRETS

#### Exemple 1 : Vampire vs Scary Face

**BDD - JOKER_TEMPLATE** :
```sql
-- Vampire
id=1, nom="Vampire", 
condition_activation='{"type":"par_carte","condition":"couleur","valeur":"coeur"}',
type_stack='mult_flat',
stack_par_unite=3

-- Scary Face
id=2, nom="Scary Face",
condition_activation='{"type":"par_carte","condition":"rang_in","valeurs":["J","Q","K"]}',
type_stack='chips',
stack_par_unite=30
```

**Code PHP - EffetGenerique (UNE SEULE CLASSE)** :
```php
class EffetGenerique implements EffetJoker {
    public function calculer(Main $main, JokerInstance $joker, Partie $partie): ResultatEffet {
        $template = $joker->getTemplate();
        
        // 1. LIRE la config du joker depuis la BDD
        $condition = json_decode($template->getConditionActivation(), true);
        $typeStack = $template->getTypeStack();
        $bonusParUnite = $template->getStackParUnite();
        
        // 2. COMPTER selon la condition
        $multiplicateur = 0;
        
        if ($condition['type'] === 'par_carte') {
            // Boucler sur les cartes de la main
            foreach ($main->getCartes() as $carte) {
                if ($this->carteRespectCondition($carte, $condition)) {
                    $multiplicateur++;  // On compte chaque carte qui match
                }
            }
        }
        
        // 3. CALCULER le bonus total
        $bonusTotal = $bonusParUnite * $multiplicateur;
        
        // 4. APPLIQUER selon le type
        return match($typeStack) {
            'chips' => new ResultatEffet(jetons: $bonusTotal),
            'mult_flat' => new ResultatEffet(mult: $bonusTotal),
            'mult_multiplicateur' => new ResultatEffet(multMultiplicateur: 1.0 + $bonusTotal/100),
            default => new ResultatEffet()
        };
    }
    
    private function carteRespectCondition(Carte $carte, array $condition): bool {
        // Logique générique pour vérifier une carte
        if ($condition['condition'] === 'couleur') {
            return $carte->getCouleur() === $condition['valeur'];
        }
        
        if ($condition['condition'] === 'rang_in') {
            return in_array($carte->getRang(), $condition['valeurs']);
        }
        
        if ($condition['condition'] === 'rang') {
            return $carte->getRang() === $condition['valeur'];
        }
        
        // ... autres conditions génériques
        return false;
    }
}
```

**RÉSULTAT avec Main = [10♥, K♣, Q♥, 5♥, J♠]** :

**Vampire** (id=1) :
```
1. Lit config: type="par_carte", condition="couleur:coeur", bonus=3
2. Compte: 10♥ (✓), Q♥ (✓), 5♥ (✓) = 3 cartes
3. Calcule: 3 cartes × 3 mult = 9 mult
4. Type = mult_flat → Retourne ResultatEffet(mult: 9)
```

**Scary Face** (id=2) :
```
1. Lit config: type="par_carte", condition="rang_in:[J,Q,K]", bonus=30
2. Compte: K♣ (✓), Q♥ (✓), J♠ (✓) = 3 figures
3. Calcule: 3 figures × 30 chips = 90 chips
4. Type = chips → Retourne ResultatEffet(jetons: 90)
```

**MÊME CODE, CONFIG DIFFÉRENTE = EFFETS DIFFÉRENTS** ✅

---

### 🎯 Exemple 2 : Constellation (avec compteur)

**BDD** :
```sql
-- JOKER_TEMPLATE
id=10, nom="Constellation",
condition_activation='{"type":"par_main","condition":"toujours"}',
type_stack='mult_flat',
stack_par_unite=1

-- JOKER_INSTANCE (dans une partie)
id=50, joker_template_id=10, compteur_stack=15
```

**Code EffetGenerique** (ajout pour compteur) :
```php
public function calculer(Main $main, JokerInstance $joker, Partie $partie): ResultatEffet {
    $template = $joker->getTemplate();
    $condition = json_decode($template->getConditionActivation(), true);
    $typeStack = $template->getTypeStack();
    $bonusParUnite = $template->getStackParUnite();
    
    $multiplicateur = 0;
    
    // Si le joker a un compteur (effets qui accumulent)
    if ($joker->getCompteurStack() !== null && $joker->getCompteurStack() > 0) {
        $multiplicateur = $joker->getCompteurStack();
    }
    // Sinon, compter selon la condition
    elseif ($condition['type'] === 'par_carte') {
        foreach ($main->getCartes() as $carte) {
            if ($this->carteRespectCondition($carte, $condition)) {
                $multiplicateur++;
            }
        }
    }
    elseif ($condition['type'] === 'par_main' && $condition['condition'] === 'toujours') {
        $multiplicateur = 1; // Effet global
    }
    
    $bonusTotal = $bonusParUnite * $multiplicateur;
    
    return match($typeStack) {
        'chips' => new ResultatEffet(jetons: $bonusTotal),
        'mult_flat' => new ResultatEffet(mult: $bonusTotal),
        // ...
    };
}
```

**RÉSULTAT** :
```
Constellation (compteur_stack=15):
1. Lit config: bonus=1 par stack
2. Compteur = 15
3. Calcule: 15 × 1 = 15 mult
4. Retourne ResultatEffet(mult: 15)
```

---

### 📋 LISTE des 100 jokers gérables par EffetGenerique

Tous ces jokers ont un **pattern commun** : compter quelque chose + appliquer un bonus

#### Catégorie 1: Bonus par couleur de carte (5 jokers)
```sql
-- Vampire: +3 mult par cœur
condition='{"type":"par_carte","condition":"couleur","valeur":"coeur"}', 
type_stack='mult_flat', stack_par_unite=3

-- Green Joker: +1 mult par trèfle (par main jouée)
condition='{"type":"par_carte","condition":"couleur","valeur":"trefle"}',
type_stack='mult_flat', stack_par_unite=1

-- Etc. pour carreau, pique...
```

#### Catégorie 2: Bonus par rang de carte (20 jokers)
```sql
-- Scary Face: +30 chips par figure
condition='{"type":"par_carte","condition":"rang_in","valeurs":["J","Q","K"]}',
type_stack='chips', stack_par_unite=30

-- Even Steven: +4 mult par carte paire (2,4,6,8,10)
condition='{"type":"par_carte","condition":"rang_in","valeurs":["2","4","6","8","10"]}',
type_stack='mult_flat', stack_par_unite=4

-- Odd Todd: +31 chips par carte impaire
condition='{"type":"par_carte","condition":"rang_in","valeurs":["3","5","7","9","A"]}',
type_stack='chips', stack_par_unite=31
```

#### Catégorie 3: Bonus selon état de la partie (15 jokers)
```sql
-- Banner: +40 chips par défausse restante
condition='{"type":"par_partie","source":"defausses_restantes"}',
type_stack='chips', stack_par_unite=40

-- Mystic Summit: +15 mult si défausses = 0
condition='{"type":"par_partie","source":"defausses_restantes","operateur":"==","valeur":0}',
type_stack='mult_flat', stack_par_unite=15

-- Raised Fist: +2 mult par carte au-dessus du rang le plus bas
condition='{"type":"par_main","calcul":"cartes_au_dessus_min"}',
type_stack='mult_flat', stack_par_unite=2
```

#### Catégorie 4: Bonus avec compteur qui accumule (25 jokers)
```sql
-- Constellation: +1 mult par planète utilisée
condition='{"type":"par_main","condition":"toujours"}',
type_stack='mult_flat', stack_par_unite=1
-- (Instance a compteur_stack)

-- Red Card: +3 mult par paquet acheté
condition='{"type":"par_main","condition":"toujours"}',
type_stack='mult_flat', stack_par_unite=3

-- Hologram: +5 mult par main jouée
condition='{"type":"par_main","condition":"toujours"}',
type_stack='mult_flat', stack_par_unite=5

-- Square Joker: +4 chips toutes les 4 cartes jouées
condition='{"type":"par_main","condition":"toujours"}',
type_stack='chips', stack_par_unite=4
```

#### Catégorie 5: Bonus selon type de main (35 jokers)
```sql
-- Stuntman: +300 chips si main contient 2 cartes
condition='{"type":"par_main","condition":"taille","valeur":2}',
type_stack='chips', stack_par_unite=300

-- Smiley Face: +5 mult par main de figures
condition='{"type":"par_main","condition":"type_main_in","valeurs":["pair","three_of_a_kind","full_house"]}',
type_stack='mult_flat', stack_par_unite=5

-- Loyalty Card: +4 mult toutes les 5 mains, +20 chips sinon
-- (Nécessiterait 2 configs ou logique légèrement plus complexe)
```

**TOTAL: ~100 jokers** couverts par une seule classe qui lit la config !

---

### ⚠️ Jokers COMPLEXES nécessitant une classe dédiée (~30)

Ces jokers ont une logique qui ne rentre PAS dans le pattern générique :

```php
// Baron: x1.5 mult PAR Roi (multiplicateur exponentiel)
// Config ne suffit pas car: 1 Roi = x1.5, 2 Rois = x2.25, 3 Rois = x3.375
class EffetBaron implements EffetJoker {
    public function calculer(Main $main, JokerInstance $joker, Partie $partie): ResultatEffet {
        $nbRois = count($main->getCartesParRang('K'));
        return new ResultatEffet(multMultiplicateur: pow(1.5, $nbRois));
    }
}

// Blueprint: COPIE l'effet du joker à droite
// Impossible avec config, nécessite récursion
class EffetBlueprint implements EffetJoker {
    public function calculer(Main $main, JokerInstance $joker, Partie $partie): ResultatEffet {
        $jokerDroite = $partie->getJokerADroite($joker);
        if (!$jokerDroite) return new ResultatEffet();
        
        $effetDroite = EffetJokerFactory::creer($jokerDroite->getTemplate()->getEffetCode());
        return $effetDroite->calculer($main, $jokerDroite, $partie);
    }
}

// Burglar: +3 mult, défausse devient 0 à la fin du round
// Nécessite effet de bord (modifier la partie)
class EffetBurglar implements EffetJoker {
    public function calculer(Main $main, JokerInstance $joker, Partie $partie): ResultatEffet {
        $partie->setDefaussesRestantes(0);  // Effet de bord!
        return new ResultatEffet(mult: 3);
    }
}
```

---

### 🏗️ Architecture finale

```php
// Factory décide quelle classe utiliser
class EffetJokerFactory {
    private static array $classesSpeciales = [
        'baron' => EffetBaron::class,
        'triboulet' => EffetTriboulet::class,
        'blueprint' => EffetBlueprint::class,
        'burglar' => EffetBurglar::class,
        // ... ~30 jokers
    ];
    
    public static function creer(string $effetCode): EffetJoker {
        if (isset(self::$classesSpeciales[$effetCode])) {
            return new (self::$classesSpeciales[$effetCode])();
        }
        
        // Par défaut: classe générique (lit la config)
        return new EffetGenerique();
    }
}
```

---

### ✅ RÉSUMÉ

**Comment 1 classe gère 100 jokers ?**

1. **La logique est dans la CONFIG** (BDD), pas dans le code PHP
2. **EffetGenerique LIT** la config et exécute des instructions génériques :
   - Compter des cartes selon critère
   - Utiliser un compteur
   - Appliquer un bonus selon type
3. **Même code + config différente = comportement différent**

**Analogie** : 
- EffetGenerique = calculatrice
- Config du joker = formule mathématique entrée
- Résultat = effet calculé

Une seule calculatrice peut faire 1000 calculs différents selon ce que vous entrez !

---

#### 1️⃣ Effets SIMPLES → Configuration JSON (80% des jokers)

Pour les jokers avec effets basiques (ajouter chips/mult selon condition), on utilise juste la config :

```php
class EffetGenerique implements EffetJoker {
    public function calculer(Main $main, JokerInstance $joker, Partie $partie): ResultatEffet {
        $template = $joker->getTemplate();
        $condition = json_decode($template->getConditionActivation(), true);
        
        $multiplicateur = 1;
        
        // Compter selon la condition
        if ($condition['type'] === 'par_carte') {
            $multiplicateur = $this->compterCartes($main, $condition);
        } elseif ($condition['type'] === 'par_main') {
            $multiplicateur = $this->verifierMain($main, $condition) ? 1 : 0;
        }
        
        // Si c'est un effet à compteur (stack)
        if ($joker->getCompteurStack() > 0) {
            $multiplicateur = $joker->getCompteurStack();
        }
        
        // Appliquer selon le type de stack
        $bonus = $template->getStackParUnite() * $multiplicateur;
        
        return match($template->getTypeStack()) {
            'chips' => new ResultatEffet(jetons: $bonus),
            'mult_flat' => new ResultatEffet(mult: $bonus),
            'mult_multiplicateur' => new ResultatEffet(multMultiplicateur: 1.0 + ($bonus / 100)),
            'xmult' => new ResultatEffet(multMultiplicateur: $bonus),
            default => new ResultatEffet()
        };
    }
}
```

**Jokers gérés par EffetGenerique** (exemples) :
- ✅ Vampire : +3 mult/cœur → config suffit
- ✅ Scary Face : +30 chips/figure → config suffit
- ✅ Constellation : +1 mult/planète → config sufficit + compteur
- ✅ Banner : +40 chips/défausse restante → config suffit
- ✅ Mystic Summit : +15 mult si défausses = 0 → config suffit

**Résultat** : ~100 jokers couverts par 1 seule classe !

---

#### 2️⃣ Effets COMPLEXES → Classes PHP dédiées (20% des jokers)

Pour les jokers avec logique complexe, on écrit une classe spécifique :

**Exemples nécessitant une classe** :

```php
// Baron: x1.5 mult par Roi dans la main jouée
class EffetBaron implements EffetJoker {
    public function calculer(Main $main, JokerInstance $joker, Partie $partie): ResultatEffet {
        $nbRois = count($main->getCartesParRang('K'));
        $multiplicateur = pow(1.5, $nbRois);  // x1.5 par Roi
        return new ResultatEffet(multMultiplicateur: $multiplicateur);
    }
}

// Triboulet: x2 mult pour chaque Roi ou Dame (effet stacking multiplicatif)
class EffetTriboulet implements EffetJoker {
    public function calculer(Main $main, JokerInstance $joker, Partie $partie): ResultatEffet {
        $nbRois = count($main->getCartesParRang('K'));
        $nbDames = count($main->getCartesParRang('Q'));
        $multiplicateur = pow(2, $nbRois + $nbDames);
        return new ResultatEffet(multMultiplicateur: $multiplicateur);
    }
}

// Fibonacci: bonus selon rang (2,3,5,8,A) - logique spécifique
class EffetFibonacci implements EffetJoker {
    private const RANGS_FIBONACCI = ['2', '3', '5', '8', 'A'];
    
    public function calculer(Main $main, JokerInstance $joker, Partie $partie): ResultatEffet {
        $multBonus = 0;
        foreach ($main->getCartes() as $carte) {
            if (in_array($carte->getRang(), self::RANGS_FIBONACCI)) {
                $multBonus += 8;
            }
        }
        return new ResultatEffet(mult: $multBonus);
    }
}

// Blueprint: copie l'effet du joker à droite - TRÈS complexe
class EffetBlueprint implements EffetJoker {
    public function calculer(Main $main, JokerInstance $joker, Partie $partie): ResultatEffet {
        $jokerDroite = $partie->getJokerADroite($joker);
        if (!$jokerDroite) {
            return new ResultatEffet();
        }
        
        // Récursion: appliquer l'effet du joker à droite
        $effetDroite = EffetJokerFactory::creer($jokerDroite->getTemplate()->getEffetCode());
        return $effetDroite->calculer($main, $jokerDroite, $partie);
    }
}
```

**Jokers nécessitant une classe** (~30 jokers) :
- Baron, Triboulet (multiplicateurs exponentiels)
- Fibonacci (rangs spécifiques)
- Blueprint, Brainstorm (copie d'effet)
- Burglar, Ramen (interaction avec défausses)
- Jokers avec conditions multi-critères complexes

---

#### 3️⃣ Factory avec fallback

```php
class EffetJokerFactory {
    private static array $classesSpeciales = [
        'baron' => EffetBaron::class,
        'triboulet' => EffetTriboulet::class,
        'fibonacci' => EffetFibonacci::class,
        'blueprint' => EffetBlueprint::class,
        // ... ~30 jokers complexes
    ];
    
    public static function creer(string $effetCode): EffetJoker {
        // Si classe spéciale existe, l'utiliser
        if (isset(self::$classesSpeciales[$effetCode])) {
            $classe = self::$classesSpeciales[$effetCode];
            return new $classe();
        }
        
        // Sinon, utiliser l'effet générique (basé sur config)
        return new EffetGenerique();
    }
}
```

---

### 📊 Bilan de l'approche hybride

| Catégorie | Nombre | Implémentation |
|-----------|--------|----------------|
| Effets simples (config JSON) | ~100 jokers | 1 classe `EffetGenerique` |
| Effets complexes (classes) | ~30 jokers | 30 classes dédiées |
| **TOTAL** | **~130 jokers** | **31 classes PHP** |

**Avantages** :
✅ Beaucoup moins de code redondant
✅ Facile d'ajouter nouveaux jokers simples (juste fixtures)
✅ Flexibilité pour effets complexes
✅ Maintenable et testable

**Pour commencer** :
- Phase 1 : `EffetGenerique` + 20 jokers simples en fixtures
- Phase 2 : Ajouter 5-10 classes pour jokers complexes populaires
- Phase 3 : Compléter progressivement

---

## 🎮 FORMULES DE CALCUL

### Calcul de base d'une main (SANS jokers)

```
SCORE = (JETONS + jetons_bonus) * (MULT + mult_bonus) * mult_multiplicateurs
```

**Exemple: Paire de 10 (niveau 1)**
```
Jetons_base = 10 (paire niveau 1)
Mult_base = 2

Cartes jouées: 10♥, 10♦, A♠, 3♣, 7♥

Jetons des cartes:
- 10♥: +10
- 10♦: +10
Total jetons = 10 (base) + 20 (cartes) = 30

Mult des cartes:
- Aucun bonus mult de cartes
Total mult = 2 (base) = 2

SCORE = 30 x 2 = 60
```

### Calcul avec jokers

```php
// Pseudo-code du moteur de calcul
function calculerScore(Main $main, Partie $partie): int {
    // 1. Calcul de base
    $mainStats = $partie->getMainStats($main->getType());
    $jetons = $mainStats->getJetonsBase();
    $mult = $mainStats->getMultBase();
    
    // 2. Jetons/Mult des cartes
    foreach ($main->getCartes() as $carte) {
        $jetons += $carte->getJetons();
        $mult += $carte->getMult();
    }
    
    // 3. Appliquer effets des jokers (dans l'ordre)
    $jetonsBonus = 0;
    $multBonus = 0;
    $multMultiplicateur = 1.0;
    
    foreach ($partie->getJokersActifs() as $jokerInstance) {
        $effet = EffetJokerFactory::creer($jokerInstance->getTemplate()->getEffetCode());
        $resultat = $effet->calculer($main, $jokerInstance, $partie);
        
        $jetonsBonus += $resultat->jetons;
        $multBonus += $resultat->mult;
        $multMultiplicateur *= $resultat->multMultiplicateur;
    }
    
    // 4. Calcul final
    $score = ($jetons + $jetonsBonus) * ($mult + $multBonus) * $multMultiplicateur;
    
    return $score;
}
```

**Exemple avec jokers**:
```
Paire de Rois avec:
- Joker Vampire (3 cartes coeur dans la main)
- Joker Baron (un Roi dans la main)

Base: 10 jetons, 2 mult
Cartes Rois: +20 jetons
Vampire: +9 mult (3 cœurs x 3)
Baron: x1.5 mult

Score = (10 + 20) x (2 + 9) x 1.5 = 30 x 11 x 1.5 = 495
```

---

## 🎮 MÉCANIQUES DE JEU - Où les stocker?

### Dans la table PARTIE:
- ✅ Mains restantes
- ✅ Défausses restantes
- ✅ Taille de main
- ✅ Ante actuel
- ✅ Argent
- ✅ Score

### Nouvelles tables nécessaires:

#### VOUCHER_INSTANCE
```
id: int PK
partie_id: int FK
voucher_template_id: int FK
actif: boolean
```

#### AMELIORATION_PARTIE (buffs temporaires)
```
id: int PK
partie_id: int FK
type: enum('main_size', 'hands', 'discards', 'joker_slots', 'consumable_slots')
valeur: int
permanent: boolean
```

---

## 📝 MODIFICATIONS À FAIRE

### 1. ✅ Refonte de l'entité Joker
**Actuellement**: Une seule classe `Joker` (confuse)
**Nouveau**: 
- `JokerTemplate` (catalogue)
- `JokerInstance` (joker dans une partie)

### 2. ✅ Modifier le formulaire de création
**Avant**: Créer un joker from scratch
**Après**: 
- Sélectionner un `JokerTemplate` existant (dropdown)
- Choisir son état (normale/foil/polychrome)
- Question** : Comment différencier mult flat, mult multiplicateur, chips ?

**Solution** : Ajouter `type_stack` dans `JOKER_TEMPLATE`

**Structure** :
```sql
JOKER_TEMPLATE:
  nom: "Vampire"
  type_stack: "mult_flat"     ← Type de bonus
  stack_par_unite: 3           ← Valeur par stack

JOKER_INSTANCE:
  joker_template_id: 42  (Vampire)
  compteur_stack: NULL   ← NULL si compté dynamiquement par cartes
  
OU (pour Constellation qui accumule):
  joker_template_id: 15  (Constellation)
  compteur_stack: 12     ← 12 planètes utilisées
```

**Types de stack possibles** :
- `chips` : Ajoute aux jetons (+30 chips)
- `mult_flat` : Ajoute au mult (+3 mult)
- `mult_multiplicateur` : Multiplie le mult (x1.5)
- `xmult` : Multiplicateur pur (x2, x3, etc.)

**Exemples concrets** :
```
Vampire: type_stack="mult_flat", stack_par_unite=3
  → 4 cœurs dans la main = +12 mult (4 x 3)

Scary Face: type_stack="chips", stack_par_unite=30
  → 3 figures dans la main = +90 chips (3 x 30)

Baron: type_stack="mult_multiplicateur", stack_par_unite=50
  → 1 Roi dans la main = x1.5 mult (1 + 50/100)

Constellation: type_stack="mult_flat", stack_par_unite=1, compteur_stack=15
  → +15 mult (15 planètes x 1)
```
2. Créer toutes les entités (User, Partie, Templates, Instances)
3. Relations Doctrine (OneToMany, ManyToOne)
4. Fixtures (données de test: 50 jokers templates)
5. Auth système (login/register)

### Phase 2: CRUD de base
6. Page d'accueil avec liste parties
7. Créer une nouvelle partie
8. Afficher état d'une partie
9. Ajouter jokers à une partie (formulaire modifié)
10. Gérer le deck de cartes

### Phase 3: Logique de jeu
11. Système de calcul de mains (Paire, Brelan, etc.)
12. Moteur d'effets de jokers (classes PHP)
13. Jouer une main (sélection cartes + calcul score)
14. Système de vouchers et améliorations

### Phase 4: Polish
15. Interface de jeu (drag & drop cartes?)
16. Animations
17. Sauvegarde auto
18. Stats et historique

---

## ❓ QUESTIONS POUR VOUS

1. **Niveau de fidélité au jeu original?**
   - Simuler TOUT Balatro? (énorme travail)
   - Focus sur mécaniques principales? (recommandé pour commencer)

2. **Interface de jeu?**
   - Simple (boutons, formulaires)
   - Avancée (drag & drop, animations)

3. **Multijoueur?**
   - Solo uniquement
   - Partage de parties (copie de deck)
   - Compétition (leaderboard)

4. **Priorités?**
   - BDD d'abord
   - Logique de jeu d'abord
   - Interface d'abord

---

## 🎯 MA RECOMMANDATION

**Commencer petit, itérer**:

1. ✅ **Semaine 1**: BDD + Auth + Fixtures
   - Créer User, Partie, JokerTemplate, JokerInstance
   - 20-30 jokers templates en fixtures
   - Login/Register

2. ✅ **Semaine 2**: CRUD Parties
   - Créer partie
   - Ajouter 3 jokers à sa partie (sélection depuis templates)
   - Afficher deck de jokers

3. ✅ **Semaine 3**: Première main jouable
   - Tirer 8 cartes
   - Sélectionner 5 cartes
   - Détecter type de main
   - Calculer score de BASE (sans effets jokers)

4. ✅ **Semaine 4**: Effets de 5 jokers simples
   - Implémenter 5 jokers basiques
   - Appliquer leurs effets au calcul

5. ✅ **Itérations suivantes**: Ajouter fonctionnalités

---

## 💬 DISCUSSION

Qu'en pensez-vous? 

- Cette architecture BDD vous convient?
- L'approche Template/Instance pour les jokers est claire?
- Par quelle phase voulez-vous commencer?
- Des questions sur les relations Doctrine?

On peut discuter et ajuster avant de coder quoi que ce soit ! 🎯

---

## ✅ RÉPONSES À VOS QUESTIONS

### 1. 📝 Comment fonctionne effet_code ?

**effet_code** est un simple **identifiant string** qui pointe vers une classe PHP.

**Exemple** :
```
JOKER_TEMPLATE:
  nom: "Vampire"
  effet_code: "vampire"  ← Juste un identifiant

CODE PHP:
  EffetJokerFactory reconnaît "vampire" → crée instance de EffetVampire
```

**Avantage** : 
- Facile à étendre (ajouter nouveau joker = créer nouvelle classe)
- Pas de logique dans la BDD (juste référence)
- Testable séparément

---

### 2. 🔍 Comment fonctionne condition_activation ?

**condition_activation** est un **JSON string** qui décrit QUAND l'effet s'active.

**Exemples réels** :
```json
// Vampire: toujours actif, compte les cœurs dans la main
{"type":"par_carte","condition":"couleur","valeur":"coeur"}

// Baron: actif si main contient un Roi
{"type":"par_main","condition":"contient_rang","valeur":"K"}

// Scary Face: actif pour chaque carte figure
{"type":"par_carte","condition":"rang_in","valeurs":["J","Q","K"]}
```

**Utilisation** :
- Le code PHP parse ce JSON
- Détermine comment appliquer l'effet
- Exemple : "par_carte" → boucle sur chaque carte, "par_main" → effet global

---

### 3. 📊 Gestion des effets qui stackent ?

**Solution** : Ajouter `compteur_stack` dans `JOKER_INSTANCE`

**Structure** :
```sql
JOKER_INSTANCE:
  id: 1
  joker_template_id: 42  (Constellation)
  compteur_stack: 15     ← Nombre de planètes utilisées
  => Effet: +15 mult
```

**Interface utilisateur** :
Quand l'utilisateur ajoute un joker à sa partie :
1. Sélectionne le joker template (dropdown)
2. Choisit l'état (normale/foil/polychrome)
3. **Définit le compteur** : input number "Nombre de stacks: [15]"

**Exemples concrets** :
- Constellation : input "Nombre de planètes utilisées"
- Red Card : input "Nombre de paquets achetés"
- Hologram : input "Nombre de mains jouées"

---

### 4. 📈 Tracking des mains jouées et niveaux ?

**Solution** : Nouvelle table `MAIN_STATS`

```sql
CREATE TABLE main_stats (
    id INT PK,
    partie_id INT FK,
    type_main ENUM('pair','flush',...),
    niveau INT DEFAULT 1,
    nb_fois_jouee INT DEFAULT 0,
    jetons_base INT,
    mult_base INT
);
```

**Interface** :
Page "Gérer ma partie" → Section "Statistiques des mains"
```
Paire : Niveau [3] | Jouée [42] fois | Jetons [10] | Mult [2]
Flush : Niveau [5] | Jouée [8]  fois | Jetons [35] | Mult [4]
...
```

Utilisateur peut modifier tous ces champs pour simuler un état avancé.

---

### 5. 🎫 Simplification avec juste Observatory ?

**✅ Excellente idée pour commencer !**

**Implémentation minimale** :
```sql
-- Table simple
CREATE TABLE voucher_instance (
    id INT PK,
    partie_id INT FK,
    observatory BOOLEAN DEFAULT false
);
```

**OU encore plus simple** :
Ajouter dans `PARTIE` :
```sql
PARTIE:
  ...
  a_observatory BOOLEAN DEFAULT false
```

**Effet** :
Si `observatory = true` → les cartes Planète donnent aussi du mult (en plus du niveau)

**Plus tard** : Si besoin, on peut transformer en table avec plusieurs vouchers.

---

## 🚀 PROCHAINE ÉTAPE CONCRÈTE

**Je propose qu'on commence par** :

### Phase 1A : Installer Doctrine et créer les entités de base

1. `composer require symfony/orm-pack`
2. Configurer la connexion BDD (SQLite pour démarrer ?)
3. Créer entités :
   - User (simple)
   - Partie (avec champs de base)
   - JokerTemplate
   - JokerInstance (avec compteur_stack)
   - MainStats
4. Générer migrations
5. Créer fixtures pour 20 jokers templates

**Résultat** : BDD fonctionnelle avec données de test

**Temps estimé** : 2-3h

---

**Voulez-vous qu'on commence maintenant ?** 

Ou préférez-vous :
- Discuter encore de l'architecture ?
- Ajouter des précisions sur certains points ?
- Commencer par une autre phase ?

Dites-moi ce qui vous semble le plus clair/flou ! 💪

