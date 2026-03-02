# Rapport Qualite - Tests & Analyse (Gestion User)

## 1) Tests statiques - PHPStan

Configuration:
- Fichier: `phpstan.neon`
- Niveau: `8`
- Fichiers analyses (6):
  - `src/Controller/Front/HomeController.php`
  - `src/Controller/Front/SecurityController.php`
  - `src/Repository/AvisRepository.php`
  - `src/Service/BadWordsFilter.php`
  - `src/Service/AiRiskScoringService.php`
  - `src/Entity/User.php`

Commande:
- `vendor\\bin\\phpstan analyse -c phpstan.neon --no-progress`

Resultat final:
- **0 erreur**

### Erreurs detectees initialement (15) et corrections

1. `SecurityController.php`: nullsafe inutile sur session (`?->`) -> remplace par `->`.
2. `SecurityController.php`: `kernel.project_dir` de type mixte -> validation `is_string` + guard.
3. `SecurityController.php`: cast implicite mixte -> passage par variable typée `string`.
4. `User.php`: `setRoles(array $roles)` sans type de valeur -> `@param list<string>`.
5. `User.php`: `getPassword(): string` pouvait retourner `null` -> fallback `''`.
6. `AvisRepository.php`: parametre `$logement` non type -> `Logement`.
7. `AvisRepository.php`: retour `findByLogement` non type -> `@return list<Avis>`.
8. `AvisRepository.php`: `getAverageRatingForLogement` non type -> `Logement`.
9. `AiRiskScoringService.php`: `json_encode` pouvait retourner `false` -> methode `encodePayload()`.
10. `AiRiskScoringService.php`: retour commande python type incertain -> construction explicite `array<int,string>`.
11. `AiRiskScoringService.php`: meme probleme sur branche Windows/Linux -> unification via boucle `foreach`.
12. `BadWordsFilter.php`: propriete `$badWords` sans type de valeur -> `@var list<string>`.
13. `BadWordsFilter.php`: `preg_replace` pouvait retourner `null` -> guard `$replaced !== null`.
14. `BadWordsFilter.php`: `censor()` pouvait retourner `null` -> retour force `string`.
15. `BadWordsFilter.php`: `getBadWords()` retour non type -> `@return list<string>` + `array_values(array_unique())`.

## 2) Tests unitaires/fonctionnels

Commande:
- `php -d extension=mbstring vendor\\bin\\phpunit -c phpunit.dist.xml --testdox`

Resultat:
- **OK (12 tests, 23 assertions)**

Fichiers de test ajoutes:
- `tests/Controller/SecurityControllerTest.php` (2 tests)
- `tests/Entity/UserTest.php` (3 tests)
- `tests/Service/AiRiskScoringServiceTest.php` (3 tests)
- `tests/Service/BadWordsFilterTest.php` (4 tests)

## 3) Analyse DoctrineDoctor

Installation:
- `composer require --dev ahmed-bhs/doctrine-doctor:^1.0 -W`

Activation:
- Bundle ajoute dans `config/bundles.php` (env `dev/test`)
- Config ajoutee: `config/packages/dev/doctrine_doctor.yaml`

Verification:
- `php bin/console debug:config doctrine_doctor --env=dev` -> configuration chargee
- `php bin/console debug:container --tag=data_collector --env=dev` -> `DoctrineDoctorDataCollector` present
- `php bin/console doctrine:schema:validate --skip-sync --env=dev` -> mapping OK

## 4) Valeur ajoutee IA dans l application

Ameliorations apportees:
1. **Fiabilite IA** (`AiRiskScoringService`):
   - serialisation JSON securisee (`encodePayload`),
   - commande Python strictement typee,
   - reduction des erreurs runtime (arguments invalides).
2. **Performance globale** (`HomeController` + `AvisRepository`):
   - suppression du N+1 sur les notes logements via agregat en requete unique.
   - effet: moins de requetes SQL, moins de risque de timeout.

## 5) Notes d execution locale

- Le PHP CLI local n avait pas `mbstring` active par defaut pour PHPUnit.
- Execution des tests faite avec `-d extension=mbstring`.
