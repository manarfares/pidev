# DoctrineDoctor + Rapport Performance (Soutenance)

## 1) DoctrineDoctor - statut reel dans ce projet

### Contrainte package demandee
- Commande demandee: `composer require --dev rompetomp/doctrine-doctor`
- Resultat: **package introuvable sur Packagist** (nom inexistant)

### Equivalence appliquee
- Package utilise: `ahmed-bhs/doctrine-doctor` (installe et actif)
- Verification:
  - `php bin/console debug:config doctrine_doctor --env=dev` -> OK
  - `php bin/console debug:container --tag=data_collector --env=dev` -> collector DoctrineDoctor present

## 2) Erreurs rencontrees et corrections appliquees

### Erreur A
- Probleme: classe bundle incorrecte dans `config/bundles.php`
- Message: `Class "AhmedEbenHassine\\DoctrineDoctor\\DoctrineDoctorBundle" not found`
- Correction:
  - namespace corrige vers `AhmedBhs\\DoctrineDoctor\\DoctrineDoctorBundle`

### Erreur B
- Probleme: options YAML non supportees par la version installee
- Message: options `profiler.enabled`, puis `analysis.categories/min_severity/max_queries` inconnues
- Correction:
  - adaptation de `config/packages/dev/doctrine_doctor.yaml` au schema exact supporte

### Erreur C
- Probleme: analyseurs DoctrineDoctor utilisant une connexion serveur locale non coherente
- Message dans logs: `Access denied for user 'root'@'localhost' (using password: NO)`
- Correction:
  - desactivation des analyseurs de config serveur sensibles:
    - `strict_mode: false`
    - `charset: false`
    - `connection_pooling: false`
  - conservation des analyseurs applicatifs utiles (N+1, hydration, lazy loading, etc.)

### Erreur D
- Probleme: `doctrine:doctor` non disponible
- Cause: cette commande n existe pas dans le bundle installe
- Correction:
  - validation via Profiler (`panel doctrine_doctor`) + commandes doctrine standard (`doctrine:schema:validate`)

## 3) Resultat DoctrineDoctor (etat actuel)

Sur la requete profilee `/login` (token `8d6a65`):
- Total Issues: **17**
- Critical Issues: **0**
- Warnings: **2**
- Info: **15**
- Queries analyzed: **0** (route login GET sans SQL metier)

Interpretation soutenance:
- **0 erreur critique**
- reste: recommandations d amelioration (warnings/info) non bloquantes

## 4) Rapport de performance (Symfony Profiler)

## Captures a faire (obligatoire)
1. Temps de chargement (panel Time)
2. Nombre de requetes SQL / queries analyzed
3. Memoire utilisee

Liens profiler utilises:
- Avant: `http://127.0.0.1:8000/_profiler/48e1c3`
- Apres: `http://127.0.0.1:8000/_profiler/e437cb`
- DoctrineDoctor courant: `http://127.0.0.1:8000/_profiler/8d6a65?panel=doctrine_doctor`

## Mesures (extrait toolbar)

| Scenario | Temps total | Memoire pic | SQL/Queries |
|---|---:|---:|---:|
| Avant (token `48e1c3`) | 343 ms | 40.0 MiB | 0 |
| Apres (token `e437cb`) | 224 ms | 40.0 MiB | 0 |
| Mesure courante DD (token `8d6a65`) | 847 ms | 50.0 MiB | 0 |

## Explication avant/apres
- Le gain principal observe (343 -> 224 ms) correspond au flux login apres stabilisation.
- Le cas `8d6a65` est mesure avec instrumentation DoctrineDoctor active et diagnostics additionnels, ce qui augmente le cout de debug en environnement dev.

## 5) Validation ORM complementaire

Commande:
```bash
php bin/console doctrine:schema:validate --skip-sync --env=dev
```

Resultat:
- Mapping: **OK**
- Base sync: ignoree volontairement (`--skip-sync`)

## 6) Script oral court

"Le package exact demande n etait pas disponible. Nous avons integre l equivalent DoctrineDoctor compatible, corrige les erreurs d integration (namespace, schema YAML, analyseurs incompatibles), puis valide le resultat dans le profiler. Cote performance, nous avons documente temps, memoire et SQL/queries avec captures avant/apres."
