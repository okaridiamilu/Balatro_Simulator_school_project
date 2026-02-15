# Résumé de la Refactorisation - Balatro Simulator

## Date de refactorisation
14 février 2026

## Objectif
Refactoriser les entités existantes pour suivre le plan d'architecture avec le pattern Template/Instance et le système d'effets configurables.

---

## 1. Nouvelles Entités Créées

### ✅ TypeStack (Enum)
**Fichier:** `src/Enum/TypeStack.php`

Enum définissant les types de stacks possibles :
- `CHIPS` - Chips additifs (+30)
- `MULT_FLAT` - Multiplicateurs plats (+3)
- `MULT_MULTIPLICATEUR` - Multiplicateurs multiplicatifs (x1.5)
- `XMULT` - X Multiplicateurs (x2)

### ✅ JokerTemplate (Entity)
**Fichier:** `src/Entity/JokerTemplate.php`

Représente la définition d'un joker dans le catalogue (le "modèle").

**Champs:**
- `id` (PK)
- `nom` (string, unique, 50 chars)
- `rarete` (RareteJoker enum)
- `description` (text)
- `image` (string, nullable)
- `effetCode` (string, 50 chars) - Code identifiant l'effet
- `conditionActivation` (json, nullable) - Configuration JSON pour les conditions
- `typeStack` (TypeStack enum) - Type de stack (chips, mult_flat, etc.)
- `stackParUnite` (float) - Valeur par unité de stack

**Relations:**
- OneToMany vers JokerInstance

### ✅ JokerInstance (Entity)
**Fichier:** `src/Entity/JokerInstance.php`

Représente une instance spécifique d'un joker dans une partie.

**Champs:**
- `id` (PK)
- `jokerTemplate_id` (FK vers JokerTemplate)
- `partie_id` (FK vers Partie)
- `etat` (EtatJoker enum, nullable) - foil, holographique, polychrome, négatif
- `ordre` (integer) - Position dans la rangée (1-5)
- `compteurStack` (integer) - Compteur de stacks accumulés

**Méthodes helper:**
- `incrementStack(int $amount = 1)` - Incrémente le stack
- `getEffetTotal(): float` - Calcule la valeur totale de l'effet
- `getNomComplet(): string` - Retourne le nom avec l'état

### ✅ Partie (Entity)
**Fichier:** `src/Entity/Partie.php`

Renommage de Session en Partie pour correspondre au plan d'architecture.

**Changements principaux:**
- Table: `session` → `partie`
- Relation: `sessions` → `parties` (dans User)
- Relation: ManyToMany vers Joker → OneToMany vers JokerInstance (ordonnée par `ordre`)
- Tables de liaison: `session_carte` → `partie_carte`, `session_consommable` → `partie_consommable`

---

## 2. Entités Modifiées

### ✅ User
**Modifications:**
- Collection `sessions` → `parties`
- Relation OneToMany vers Session → vers Partie
- Méthodes: `getSessions()` → `getParties()`, `addSession()` → `addPartie()`, etc.

### ✅ Carte
**Modifications:**
- Collection `sessions` → `parties`
- Relation ManyToMany vers Session → vers Partie  
- Méthodes: `getSessions()` → `getParties()`, etc.

### ✅ Consommable
**Modifications:**
- Collection `sessions` → `parties`
- Relation ManyToMany vers Session → vers Partie
- Méthodes: `getSessions()` → `getParties()`, etc.

---

## 3. Formulaires

### ✅ JokerTemplateType (Nouveau)
**Fichier:** `src/Form/JokerTemplateType.php`

Formulaire pour créer/éditer des JokerTemplate (catalogue).

**Champs:**
- `nom` - Nom du joker
- `rarete` - Rareté (EnumType)
- `description` - Description
- `image` - URL de l'image
- `effetCode` - Code d'effet
- `typeStack` - Type de stack (EnumType avec labels personnalisés)
- `stackParUnite` - Valeur par unité de stack (NumberType avec step 0.01)

### ✅ JokerFilterType (Modifié)
**Modifications:**
- ❌ Supprimé: champ `etat` (n'existe plus dans JokerTemplate)
- ✅ Conservé: champs `nom` et `rarete`

### ⚠️ JokerType (Ancien - Non utilisé)
**Statut:** Conservé tel quel mais **non utilisé** actuellement.

**À faire plus tard:**
- Supprimer ou transformer en formulaire pour créer des JokerInstance dans une partie
- Si transformation: ajouter sélection du template + champs etat/ordre/compteurStack

---

## 4. Contrôleurs

### ✅ HomeController
**Modifications:**

**Route `/about`:**
- Repository: `Joker` → `JokerTemplate`
- Filtre: Suppression du filtre par `etat`

**Route `/joker/new`:**
- Entity: `Joker` → `JokerTemplate`
- Form: `JokerType` → `JokerTemplateType`
- **IMPORTANT:** Ajout de la sauvegarde en base de données (`$em->persist()` + `$em->flush()`)
- Message: "validé avec succès ! (Note: non sauvegardé car pas de BDD)" → "créé avec succès !"

**Méthode `filterJokers()`:**
- Type hint: `Joker` → `JokerTemplate`
- Suppression du filtre par `etat`

---

## 5. Fichiers NON Modifiés (à garder tel quel)

Les entités suivantes n'ont **pas été modifiées** lors de cette refactorisation :
- ✅ `HandLevel.php`
- ✅ `Voucher.php`
- ✅ `Y.php`
- ✅ Tous les enums (sauf ajout de TypeStack) :
  - `EtatJoker.php`
  - `RareteJoker.php`
  - `CarteColor.php`
  - `CarteNumber.php`
  - `CarteStatus.php`
  - `CarteStatusMatter.php`
  - `CarteStatusSeal.php`
  - `ConsommableCategory.php`
  - `ConsommableStatus.php`
  - `ConsommableType.php`

---

## 6. Prochaines Étapes

### Étape 1: Migrations de base de données
```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

### Étape 2: Fixtures
Installer le bundle de fixtures :
```bash
composer require --dev doctrine/doctrine-fixtures-bundle
```

Créer des fixtures pour 20 JokerTemplate basés sur les vrais jokers de Balatro :
- Vampire (xmult, +0.5 par carte Enhanced jouée)
- Baron (xmult, x1.5 si main contient un Roi)
- Constellation (xmult, +0.1 par carte jouée avec seal Planet)
- etc.

### Étape 3: Classe EffetGenerique
Créer le service `src/Service/EffetGenerique.php` qui :
- Lit `conditionActivation` (JSON)
- Lit `typeStack` et `stackParUnite`
- Applique la logique conditionnelle
- Retourne les modifications de chips/mult

### Étape 4: Classes d'effets complexes
Pour les 30 jokers avec logique complexe, créer des classes dédiées :
- `src/Service/Effet/VampireEffet.php`
- `src/Service/Effet/BaronEffet.php`
- etc.

### Étape 5: Système de jeu
- Créer les contrôleurs pour gérer les parties
- Implémenter la logique de jeu (jouer une main, calculer les scores)
- Créer les templates Twig pour l'interface de jeu

---

## 7. Architecture Finale

```
JokerTemplate (Catalogue)
    ├─ nom, rarete, description, image
    ├─ effetCode (identifiant unique)
    ├─ conditionActivation (JSON config)
    ├─ typeStack (chips/mult_flat/mult_multiplicateur/xmult)
    └─ stackParUnite (valeur par stack)
    
JokerInstance (Dans une partie)
    ├─ jokerTemplate (référence au modèle)
    ├─ partie (référence à la partie)
    ├─ etat (foil/holographique/polychrome/négatif)
    ├─ ordre (position 1-5)
    └─ compteurStack (stacks accumulés)

Partie (Anciennement Session)
    ├─ identifiant, money, hand, discard
    ├─ user (ManyToOne)
    ├─ jokers (OneToMany vers JokerInstance, ordonnée)
    ├─ cartes (ManyToMany)
    ├─ consommables (ManyToMany)
    ├─ handLevel (OneToOne)
    └─ voucher (OneToOne, nullable)
```

---

## 8. Avantages de cette Architecture

✅ **Séparation des préoccupations**
- Template = définition/catalogue
- Instance = état dans une partie

✅ **Configuration-driven**
- 100 jokers simples gérés par EffetGenerique avec config JSON
- Seulement 30 classes pour les jokers complexes

✅ **Évolutivité**
- Ajouter un nouveau joker simple = INSERT en base de données
- Pas besoin de coder une nouvelle classe

✅ **Performance**
- Stack stocké dans JokerInstance
- Calcul facile: `stackParUnite * compteurStack`

✅ **Flexibilité**
- Même template peut avoir plusieurs instances
- Chaque instance a son propre état et compteur

---

## Statut: ✅ REFACTORISATION TERMINÉE

Tous les fichiers ont été mis à jour avec succès.
Aucune erreur de syntaxe détectée.
Prêt pour la génération des migrations.
