# Tests et Pages d'Erreur

## Tests Unitaires

### Tests créés

- **PartieTest.php** : 9 tests pour l'entité Partie
  - Initialisation d'une partie
  - Calcul des slots de jokers
  - Gestion des jokers négatifs (ajout de slots)
  - Jokers multiples (normaux et négatifs)
  - Toggle de l'Observatoire
  - Suppression de jokers
  - Collections de cartes et consommables

### Exécuter les tests

```bash
# Tous les tests
php bin/phpunit

# Tests spécifiques à l'entité Partie
php bin/phpunit tests/Entity/PartieTest.php

# Tests avec couverture (si xdebug est activé)
php bin/phpunit --coverage-html coverage
```

### Résultats attendus

```
PHPUnit 12.5.8 by Sebastian Bergmann and contributors.

.........                                                           9 / 9 (100%)

Time: 00:00.017, Memory: 16.00 MB

OK (9 tests, 31 assertions)
```

## Pages d'Erreur Personnalisées

### Pages créées

Les pages d'erreur personnalisées se trouvent dans `templates/bundles/TwigBundle/Exception/` :

- **error404.html.twig** : Page "404 - Page non trouvée"
  - Affiche un message convivial
  - Propose un retour à l'accueil
  - Lien vers "Mes parties" si connecté

- **error403.html.twig** : Page "403 - Accès refusé"
  - Message d'accès refusé
  - Propose de se connecter si non authentifié
  - Lien vers l'accueil

- **error500.html.twig** : Page "500 - Erreur serveur"
  - Message d'erreur serveur générique
  - Bouton pour réessayer
  - Lien vers l'accueil

- **error.html.twig** : Page d'erreur générique
  - Affiche le code d'erreur et le message
  - En mode dev : affiche le message d'exception détaillé
  - Liens vers l'accueil et les parties

### Test des pages d'erreur

#### En mode développement

Symfony affiche le profiler par défaut. Pour visualiser les pages d'erreur personnalisées en mode dev :

```
# Accéder directement aux pages d'erreur via l'URL spéciale
http://localhost:8000/_error/404
http://localhost:8000/_error/403
http://localhost:8000/_error/500
```

#### En mode production

Les pages s'affichent automatiquement quand une erreur se produit :

1. Temporairement, modifier `.env` :
   ```
   APP_ENV=prod
   APP_DEBUG=0
   ```

2. Vider le cache :
   ```bash
   php bin/console cache:clear --env=prod
   ```

3. Relancer le serveur

4. Tester :
   - Page 404 : accéder à une URL inexistante `/page-qui-nexiste-pas`
   - Page 403 : tenter d'accéder à une partie d'un autre utilisateur
   - Page 500 : provoquer une erreur dans le code

5. Remettre en mode dev après les tests

### Caractéristiques des pages d'erreur

- ✅ Design cohérent avec le reste du site (Bootstrap)
- ✅ Messages clairs et conviviaux
- ✅ Actions contextuelles (retour accueil, connexion, mes parties)
- ✅ Responsive (mobile-friendly)
- ✅ Icônes Bootstrap Icons
- ✅ Codes couleur adaptés (bleu pour 404, rouge pour 403, orange pour 500)

## Couverture de Tests

Les tests actuels couvrent :

- ✅ Logique métier de l'entité Partie
- ✅ Calcul des slots de jokers (avec et sans jokers négatifs)
- ✅ Gestion des collections (jokers, cartes, consommables)
- ✅ Toggle de fonctionnalités (Observatoire)
- ✅ Ajout/suppression d'éléments

### Extensions possibles

Pour étendre les tests :

1. **Tests de contrôleurs** (WebTestCase)
   - Tester les routes et redirections
   - Vérifier l'authentification
   - Tester les formulaires

2. **Tests d'entités supplémentaires**
   - JokerTemplate
   - HandLevel
   - Carte
   - Consommable

3. **Tests d'intégration**
   - Import/Export JSON
   - Gestion des parties
   - Authentification

4. **Tests E2E** (Panther)
   - Scénarios utilisateur complets
   - Tests JavaScript (autocomplete, etc.)
