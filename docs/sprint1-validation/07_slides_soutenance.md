# Slide 1 - TESTS STATIQUES (PHPStan)

## Objectif
- Elever la qualite du code avec une analyse statique stricte.

## Configuration
- Outil: `phpstan/phpstan`
- Niveau: `level: 8`
- Fichier: `phpstan.neon`
- Perimetre: 6 fichiers (`Controller`, `Service`, `Entity`, `Repository`)

## Execution
```bash
vendor\bin\phpstan analyse -c phpstan.neon --no-progress
```

## Resultats
- Erreurs initiales: **15**
- Erreurs finales: **0**

## Corrections majeures
- Typage strict des tableaux (`list<string>`, `list<Avis>`)
- Gestion explicite des `null` (`preg_replace`, `json_encode`)
- Correction des types mixtes (`kernel.project_dir`)
- Durcissement des signatures de methodes et retours

## Message oral (20s)
- "Nous avons applique PHPStan niveau 8 sur 6 fichiers critiques. On est passe de 15 erreurs a 0, avec des corrections de typage, null-safety et robustesse runtime."

---

# Slide 2 - TESTS UNITAIRES / FONCTIONNELS

## Objectif
- Verifier le comportement metier et web sur le scope User.

## Suite de tests
- Framework: `PHPUnit`
- Commande:
```bash
php -d extension=mbstring vendor\bin\phpunit -c phpunit.dist.xml --testdox
```

## Couverture (4 fichiers, 12 tests)
- `tests/Controller/SecurityControllerTest.php` (2)
- `tests/Entity/UserTest.php` (3)
- `tests/Service/AiRiskScoringServiceTest.php` (3)
- `tests/Service/BadWordsFilterTest.php` (4)

## Resultat final
- **OK (12 tests, 23 assertions)**
- 0 echec, 0 erreur

## Exemples verifies
- Acces page login et redirection utilisateur anonyme
- Cycle utilisateur: suspendre / reactiver / roles
- Logique IA: seuil et activation auto-suspension
- Filtrage/censure des mots interdits

## Message oral (20s)
- "Nous avons depasse le minimum demande: 12 tests automatises en vert, repartis sur Controller, Entity et Services."

---

# Slide 3 - ANALYSE DOCTRINE DOCTOR

## Objectif
- Detecter les problemes Doctrine a l execution (performance + qualite).

## Integration
- Package: `ahmed-bhs/doctrine-doctor`
- Bundle active en `dev/test` dans `config/bundles.php`
- Config: `config/packages/dev/doctrine_doctor.yaml`

## Verifications effectuees
```bash
php bin/console debug:config doctrine_doctor --env=dev
php bin/console debug:container --tag=data_collector --env=dev
php bin/console doctrine:schema:validate --skip-sync --env=dev
```

## Resultats
- Config chargee correctement
- Data collector DoctrineDoctor detecte dans le profiler
- Mapping Doctrine valide

## Point important
- Ajustement de configuration necessaire (certaines options non supportees par la version installee) -> corrige.

## Message oral (20s)
- "DoctrineDoctor est integre et visible dans le profiler Symfony. Nous avons valide la config et la coherence du mapping Doctrine."

---

# Slide 4 - VALEUR AJOUTEE IA ET IMPACT

## Objectif
- Renforcer la securite et la performance via IA + optimisation technique.

## Valeur IA
- Service: `AiRiskScoringService`
- Ameliorations:
  - Encodage JSON fiable (`encodePayload`)
  - Construction commande Python typee
  - Reduction des erreurs runtime sur scoring de risque

## Valeur performance
- Correction N+1 sur page d accueil:
  - `HomeController` + `AvisRepository`
  - Passage a une aggregation en requete unique pour notes/compteurs

## Impact concret
- Meilleure stabilite des routes critiques
- Reduction du risque de timeout
- Code plus maintenable et auditable

## Message oral (25s)
- "Nous avons ajoute de la valeur technique et metier: IA plus robuste pour le risque utilisateur, et optimisation SQL anti N+1 qui accelere l affichage et diminue les timeouts."

---

# Slide Bonus - Commandes de demo live (si demande jury)

```bash
vendor\bin\phpstan analyse -c phpstan.neon --no-progress
php -d extension=mbstring vendor\bin\phpunit -c phpunit.dist.xml --testdox
php bin/console debug:config doctrine_doctor --env=dev
```

Resultats attendus:
- PHPStan: `No errors`
- PHPUnit: `OK (12 tests, 23 assertions)`
- DoctrineDoctor: configuration visible
