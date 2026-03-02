# Sprint Backlog (Gestion User) - Base sur le Product Backlog corrige

## Source officielle des User Stories
- Fichier source: `smart-rental-platform/MD/Smart_Rental_Platform_Product_Backlog.xlsx`
- Feuille: `Product Backlog`
- Epic cible: `Gestion User`
- User Stories disponibles dans le backlog corrige: `US1.1` a `US1.6`

## User Stories selectionnees pour le Sprint 1 (Gestion User)

| ID | User Story (depuis Product Backlog) | Priority | Story Points |
|---|---|---|---:|
| US1.1 | As a visitor, I want to create an account so that I can use the platform. | High | 3 |
| US1.2 | As a user, I want to log in and log out so that my account is secure. | High | 2 |
| US1.4 | As an admin, I want to monitor suspicious user activity so that I can detect scammers. | High | 8 |
| US1.5 | As an admin, I want to suspend or ban abusive users so that the platform remains safe. | High | 5 |
| US1.6 | As an admin, I want to manage user roles and account status so that I can control access. | Medium | 3 |

Total Sprint (scope User): **21 SP**

## Detail technique par User Story (point de vue developpeur)

### US1.1 - Create account
1. Creer/valider le formulaire Symfony d inscription (`RegistrationFormType`).
2. Ajouter les contraintes de validation (email, mot de passe, champs requis).
3. Hasher le mot de passe avec `UserPasswordHasherInterface`.
4. Affecter les valeurs par defaut (`ROLE_USER`, `isVerified=false`).
5. Persister l utilisateur via Doctrine (`persist/flush`).
6. Gerer les erreurs de formulaire et retours UI (flash + HTTP 422 si invalide).
7. Rediriger vers la page de connexion apres succes.

### US1.2 - Login / Logout securise
1. Configurer l authentification Symfony (`LoginFormAuthenticator`, firewall `main`).
2. Construire l ecran login avec affichage des erreurs d auth.
3. Gerer la session utilisateur (creation session apres auth valide).
4. Implementer la route logout et invalidation session.
5. Verifier la protection CSRF sur formulaires sensibles.
6. Ajouter les controles d acces par role sur routes front/back.
7. Prevoir timeout de session via configuration de securite.

### US1.4 - Monitor suspicious user activity
1. Ajouter les attributs metier dans `User`:
   - `failedLoginAttempts`
   - `suspiciousActivityScore`
   - `lastFailedLoginAt`, `lastLoginAt`, `lastLoginIp`
2. Implementer les methodes domaine:
   - `recordFailedLogin()`
   - `recordSuccessfulLogin()`
   - `increaseSuspiciousActivity()/decreaseSuspiciousActivity()`
3. Ajouter les requetes repository:
   - comptage utilisateurs a risque
   - tri par risque (`findMostSuspiciousUsers`)
4. Integrer les indicateurs dans dashboard admin (`getAdminStats` + vue admin).
5. Ajouter affichage de la liste des comptes suspects dans l interface admin.
6. Journaliser les evenements de securite utiles (date, utilisateur, type evenement).

### US1.5 - Suspend / Ban abusive users
1. Definir les etats de compte (`active`, `suspended`, `banned`) dans l entite `User`.
2. Implementer les transitions de statut (`suspend()`, `ban()`, `activateAccount()`).
3. Exposer action admin securisee pour changement de statut (route POST + CSRF).
4. Bloquer l acces applicatif pour comptes suspendus/bannis (user checker / authentification).
5. Mettre a jour l UI admin pour actions suspendre/reactiver/bannir.
6. Tracer les changements de statut (historique action admin).
7. Ajouter tests fonctionnels: compte suspendu ne reserve pas, compte banni ne se connecte pas.

### US1.6 - Manage user roles and account status
1. Construire ecran admin users (liste + recherche + filtres role/statut + tri).
2. Implementer requete repository `findForAdmin(search, role, status, sort)`.
3. Ajouter endpoint admin de mise a jour statut (et role si active dans scope sprint).
4. Verifier toutes les actions admin par `ROLE_ADMIN`.
5. Ajouter export CSV des utilisateurs avec role/statut.
6. Ajouter garde-fous:
   - email unique
   - validation des statuts autorises
   - prevention incoherences de role
7. Ajouter tests de non-regression sur filtrage, tri, actions admin.

## Mapping rapide avec le code existant
- Inscription: `src/Controller/Front/RegistrationController.php`
- Connexion: `src/Controller/Front/SecurityController.php`
- Profil utilisateur: `src/Controller/Front/ProfileController.php`
- Administration users: `src/Controller/Back/AdminController.php`
- Requetes users admin/risk: `src/Repository/UserRepository.php`
- Etats/score securite user: `src/Entity/User.php`

## Proposition d explication orale (2 stories)
- Story CRUD: **US1.6** (lire/filtrer/modifier statut utilisateur cote admin).
- Story avancee: **US1.4** (monitoring risque via score activite suspecte et indicateurs dashboard).
