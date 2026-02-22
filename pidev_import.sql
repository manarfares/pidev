-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : dim. 22 fév. 2026 à 04:28
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `pidev`
--

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

CREATE TABLE `avis` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `note` int(11) NOT NULL,
  `commentaire` longtext NOT NULL,
  `date_creation` datetime NOT NULL,
  `reponse_hote` longtext DEFAULT NULL,
  `date_reponse` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `avis`
--

INSERT INTO `avis` (`id`, `reservation_id`, `note`, `commentaire`, `date_creation`, `reponse_hote`, `date_reponse`) VALUES
(1, 3, 4, 'try try try', '2026-02-22 01:43:14', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

CREATE TABLE `categorie` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `covoiturage`
--

CREATE TABLE `covoiturage` (
  `id` int(11) NOT NULL,
  `conducteur_id` int(11) NOT NULL,
  `depart` varchar(255) NOT NULL,
  `destination` varchar(255) NOT NULL,
  `date_depart` datetime NOT NULL,
  `places` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `covoiturage`
--

INSERT INTO `covoiturage` (`id`, `conducteur_id`, `depart`, `destination`, `date_depart`, `places`) VALUES
(1, 3, 'ggg', 'ggggggg', '2026-02-28 02:38:00', 5),
(2, 3, 'Behaya, Délégation Mateur, Gouvernorat Bizerte, Tunisie', 'Edkhila, Délégation Tebourba, Gouvernorat La Manouba, Tunisie', '2026-02-28 02:52:00', 5),
(3, 3, '51.5739, 1.2948', 'Neuer Weg, Geyer, Verwaltungsgemeinschaft Geyer, Erzgebirgskreis, Saxe, 09468, Allemagne', '2026-03-07 03:14:00', 4),
(4, 3, '48.5788, 2.2565', 'Les Besneries, Parcé-sur-Sarthe, La Flèche, Sarthe, Pays de la Loire, France métropolitaine, 72300, France', '2026-03-07 04:21:00', 2),
(5, 4, '36.8503, 10.1826', 'ggggggg', '2026-02-28 04:22:00', 11);

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `foyer`
--

CREATE TABLE `foyer` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `logement`
--

CREATE TABLE `logement` (
  `id` int(11) NOT NULL,
  `proprietaire_id` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `adresse` varchar(255) NOT NULL,
  `prix_par_nuit` decimal(10,2) NOT NULL,
  `nombre_chambres` int(11) NOT NULL,
  `disponible` tinyint(1) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `capacite` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `logement`
--

INSERT INTO `logement` (`id`, `proprietaire_id`, `titre`, `description`, `adresse`, `prix_par_nuit`, `nombre_chambres`, `disponible`, `image`, `type`, `capacite`) VALUES
(1, 3, 'Appartement Parisien Charmant', 'Magnifique appartement au cœur de Paris avec vue imprenable sur la Tour Eiffel. Idéal pour un séjour romantique ou des vacances en famille.', '15 Rue de la Paix, 75002 Paris', 150.00, 2, 1, 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&h=600&fit=crop', 'Appartement', 4),
(2, 3, 'Villa Moderne avec Piscine', 'Superbe villa contemporaine avec piscine privée, jardin paysager et vue panoramique. Parfait pour des vacances de luxe en famille.', '42 Avenue des Mimosas, 06400 Cannes', 350.00, 4, 1, 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800&h=600&fit=crop', 'Villa', 8),
(3, 3, 'Studio Cosy Centre-Ville', 'Studio moderne et fonctionnel en plein centre-ville. Proche de toutes commodités, transports et attractions touristiques.', '8 Rue du Commerce, 69002 Lyon', 75.00, 1, 1, 'https://images.unsplash.com/photo-1540518614846-7eded433c457?w=800&h=600&fit=crop', 'Studio', 2),
(4, 3, 'Maison de Campagne Authentique', 'Charmante maison de campagne rénovée avec goût. Grand jardin, cheminée et calme absolu. Idéal pour se ressourcer.', '23 Chemin des Vignes, 84220 Gordes', 180.00, 3, 1, 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=800&h=600&fit=crop', 'Maison', 6),
(5, 3, 'Loft Industriel Moderne', 'Loft spacieux au style industriel dans un ancien entrepôt rénové. Hauteur sous plafond exceptionnelle et grande luminosité.', '56 Rue des Artistes, 13001 Marseille', 120.00, 2, 1, 'https://images.unsplash.com/photo-1493809842364-78817add7ffb?w=800&h=600&fit=crop', 'Loft', 4),
(6, 3, 'Appartement Parisien Charmant', 'Magnifique appartement au cœur de Paris avec vue imprenable sur la Tour Eiffel. Idéal pour un séjour romantique ou des vacances en famille.', '15 Rue de la Paix, 75002 Paris', 150.00, 2, 1, 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&h=600&fit=crop', 'Appartement', 4),
(7, 3, 'Villa Moderne avec Piscine', 'Superbe villa contemporaine avec piscine privée, jardin paysager et vue panoramique. Parfait pour des vacances de luxe en famille.', '42 Avenue des Mimosas, 06400 Cannes', 350.00, 4, 1, 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800&h=600&fit=crop', 'Villa', 8),
(8, 3, 'Studio Cosy Centre-Ville', 'Studio moderne et fonctionnel en plein centre-ville. Proche de toutes commodités, transports et attractions touristiques.', '8 Rue du Commerce, 69002 Lyon', 75.00, 1, 1, 'https://images.unsplash.com/photo-1540518614846-7eded433c457?w=800&h=600&fit=crop', 'Studio', 2),
(9, 3, 'Maison de Campagne Authentique', 'Charmante maison de campagne rénovée avec goût. Grand jardin, cheminée et calme absolu. Idéal pour se ressourcer.', '23 Chemin des Vignes, 84220 Gordes', 180.00, 3, 1, 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=800&h=600&fit=crop', 'Maison', 6),
(10, 3, 'Loft Industriel Moderne', 'Loft spacieux au style industriel dans un ancien entrepôt rénové. Hauteur sous plafond exceptionnelle et grande luminosité.', '56 Rue des Artistes, 13001 Marseille', 120.00, 2, 1, 'https://images.unsplash.com/photo-1493809842364-78817add7ffb?w=800&h=600&fit=crop', 'Loft', 4),
(11, 3, 'Appartement Parisien Charmant', 'Magnifique appartement au cœur de Paris avec vue imprenable sur la Tour Eiffel. Idéal pour un séjour romantique ou des vacances en famille.', '15 Rue de la Paix, 75002 Paris', 150.00, 2, 1, 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&h=600&fit=crop', 'Appartement', 4),
(12, 3, 'Villa Moderne avec Piscine', 'Superbe villa contemporaine avec piscine privée, jardin paysager et vue panoramique. Parfait pour des vacances de luxe en famille.', '42 Avenue des Mimosas, 06400 Cannes', 350.00, 4, 1, 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800&h=600&fit=crop', 'Villa', 8),
(13, 3, 'Studio Cosy Centre-Ville', 'Studio moderne et fonctionnel en plein centre-ville. Proche de toutes commodités, transports et attractions touristiques.', '8 Rue du Commerce, 69002 Lyon', 75.00, 1, 1, 'https://images.unsplash.com/photo-1540518614846-7eded433c457?w=800&h=600&fit=crop', 'Studio', 2),
(14, 3, 'Maison de Campagne Authentique', 'Charmante maison de campagne rénovée avec goût. Grand jardin, cheminée et calme absolu. Idéal pour se ressourcer.', '23 Chemin des Vignes, 84220 Gordes', 180.00, 3, 1, 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=800&h=600&fit=crop', 'Maison', 6),
(15, 3, 'Loft Industriel Moderne', 'Loft spacieux au style industriel dans un ancien entrepôt rénové. Hauteur sous plafond exceptionnelle et grande luminosité.', '56 Rue des Artistes, 13001 Marseille', 120.00, 2, 1, 'https://images.unsplash.com/photo-1493809842364-78817add7ffb?w=800&h=600&fit=crop', 'Loft', 4),
(16, 3, 'trygggggggg', 'trytrygggggggggggggggggggggggggggggggggggggggggggggggggg', 'trytrytryfsssssssssssssssssssssssssss', 2520.00, 2, 1, NULL, 'Studio', 15),
(17, 3, 'trygggggggg', 'yhu\'j(èik-_olèçpmà_pçl-o_è(i-j\'uh(\"y\'ét', 'sdfrgtyrutiyop^', 152.00, 25, 1, NULL, 'Loft', 8787);

-- --------------------------------------------------------

--
-- Structure de la table `materiel`
--

CREATE TABLE `materiel` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `messenger_messages`
--

CREATE TABLE `messenger_messages` (
  `id` bigint(20) NOT NULL,
  `body` longtext NOT NULL,
  `headers` longtext NOT NULL,
  `queue_name` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `available_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `delivered_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `participant`
--

CREATE TABLE `participant` (
  `id` int(11) NOT NULL,
  `passager_id` int(11) NOT NULL,
  `covoiturage_id` int(11) NOT NULL,
  `statut` varchar(50) NOT NULL DEFAULT 'en_attente',
  `date_creation` datetime NOT NULL DEFAULT current_timestamp(),
  `message` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `participant`
--

INSERT INTO `participant` (`id`, `passager_id`, `covoiturage_id`, `statut`, `date_creation`, `message`) VALUES
(1, 2, 1, 'confirme', '2026-02-22 02:39:23', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `reservation`
--

CREATE TABLE `reservation` (
  `id` int(11) NOT NULL,
  `logement_id` int(11) NOT NULL,
  `locataire_id` int(11) NOT NULL,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime NOT NULL,
  `montant_total` decimal(10,2) NOT NULL,
  `statut` varchar(50) NOT NULL,
  `date_creation` datetime NOT NULL,
  `nombre_personnes` int(11) DEFAULT NULL,
  `notes_speciales` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `reservation`
--

INSERT INTO `reservation` (`id`, `logement_id`, `locataire_id`, `date_debut`, `date_fin`, `montant_total`, `statut`, `date_creation`, `nombre_personnes`, `notes_speciales`) VALUES
(3, 1, 2, '2026-01-22 01:41:55', '2026-01-25 01:41:55', 450.00, 'confirmee', '2026-01-22 01:41:55', 2, NULL),
(4, 17, 2, '2026-02-28 00:00:00', '2026-03-05 00:00:00', 760.00, 'confirmee', '2026-02-22 02:13:57', 1, NULL),
(5, 2, 2, '2026-02-22 00:00:00', '2026-03-05 00:00:00', 3850.00, 'refusee', '2026-02-22 02:18:18', 4, NULL),
(6, 3, 2, '2026-02-22 00:00:00', '2026-02-24 00:00:00', 150.00, 'confirmee', '2026-02-22 02:19:34', 2, NULL),
(7, 1, 2, '2026-02-24 00:00:00', '2026-02-26 00:00:00', 300.00, 'confirmee', '2026-02-22 03:18:45', 2, NULL),
(8, 2, 2, '2026-03-25 00:00:00', '2026-04-04 00:00:00', 3500.00, 'en_attente', '2026-02-22 03:57:02', 1, NULL),
(9, 2, 2, '2026-05-07 00:00:00', '2026-06-02 00:00:00', 9100.00, 'en_attente', '2026-02-22 03:59:11', 1, NULL),
(10, 10, 2, '2026-03-06 00:00:00', '2026-03-08 00:00:00', 240.00, 'en_attente', '2026-02-22 04:03:47', 3, NULL),
(11, 16, 2, '2026-03-03 00:00:00', '2026-03-08 00:00:00', 12600.00, 'en_attente', '2026-02-22 04:11:35', 12, NULL),
(12, 5, 4, '2026-03-18 00:00:00', '2026-04-01 00:00:00', 1680.00, 'confirmee', '2026-02-22 04:15:40', 2, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `reset_password_request`
--

CREATE TABLE `reset_password_request` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `selector` varchar(20) NOT NULL,
  `hashed_token` varchar(100) NOT NULL,
  `requested_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `expires_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `service`
--

CREATE TABLE `service` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `email` varchar(180) NOT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`roles`)),
  `password` varchar(255) NOT NULL,
  `is_verified` tinyint(1) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `email`, `roles`, `password`, `is_verified`, `nom`, `prenom`) VALUES
(1, 'test@example.com', '[\"ROLE_ADMIN\"]', '$2y$13$DAFPydhsoJGBvzf.sG9DaOS7wUi9NCzmrqoyUUx6LZZjHpfJrK1La', 1, NULL, NULL),
(2, 'user@test.com', '[\"ROLE_USER\"]', '$2y$13$jWiPz0SowK0g1YBob4.TQeJndh67t/uHh2U85mEmAn6G.Sxz4.VGi', 1, 'Dupont', 'Jean'),
(3, 'host@test.com', '[\"ROLE_HOST\"]', '$2y$13$4SphgAy0/d7BZwhPmn5e3OtO/z/KLKCGeJYOD4gx00C4nhme9iZ8C', 1, 'Martin', 'Marie'),
(4, 'admin@test.com', '[\"ROLE_ADMIN\"]', '$2y$13$3QaoBIGuwzyvikODnG7ydu7PpEyCA1vEIlE22VOiVaRS5Geq.dwTq', 1, 'System', 'Admin');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `avis`
--
ALTER TABLE `avis`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_8F91ABF0B83297E7` (`reservation_id`);

--
-- Index pour la table `categorie`
--
ALTER TABLE `categorie`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `covoiturage`
--
ALTER TABLE `covoiturage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_28C79E89F16F4AC6` (`conducteur_id`);

--
-- Index pour la table `doctrine_migration_versions`
--
ALTER TABLE `doctrine_migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Index pour la table `foyer`
--
ALTER TABLE `foyer`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `logement`
--
ALTER TABLE `logement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_F0FD445776C50E4A` (`proprietaire_id`);

--
-- Index pour la table `materiel`
--
ALTER TABLE `materiel`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750` (`queue_name`,`available_at`,`delivered_at`,`id`);

--
-- Index pour la table `participant`
--
ALTER TABLE `participant`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_D79F6B1171A51189` (`passager_id`),
  ADD KEY `IDX_D79F6B1162671590` (`covoiturage_id`);

--
-- Index pour la table `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_42C8495558ABF955` (`logement_id`),
  ADD KEY `IDX_42C84955D8A38199` (`locataire_id`);

--
-- Index pour la table `reset_password_request`
--
ALTER TABLE `reset_password_request`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_7CE748AA76ED395` (`user_id`);

--
-- Index pour la table `service`
--
ALTER TABLE `service`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `avis`
--
ALTER TABLE `avis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `categorie`
--
ALTER TABLE `categorie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `covoiturage`
--
ALTER TABLE `covoiturage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `foyer`
--
ALTER TABLE `foyer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `logement`
--
ALTER TABLE `logement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `materiel`
--
ALTER TABLE `materiel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `participant`
--
ALTER TABLE `participant`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `reservation`
--
ALTER TABLE `reservation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `reset_password_request`
--
ALTER TABLE `reset_password_request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `service`
--
ALTER TABLE `service`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `avis`
--
ALTER TABLE `avis`
  ADD CONSTRAINT `FK_8F91ABF0B83297E7` FOREIGN KEY (`reservation_id`) REFERENCES `reservation` (`id`);

--
-- Contraintes pour la table `covoiturage`
--
ALTER TABLE `covoiturage`
  ADD CONSTRAINT `FK_28C79E89F16F4AC6` FOREIGN KEY (`conducteur_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `logement`
--
ALTER TABLE `logement`
  ADD CONSTRAINT `FK_F0FD445776C50E4A` FOREIGN KEY (`proprietaire_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `participant`
--
ALTER TABLE `participant`
  ADD CONSTRAINT `FK_D79F6B1162671590` FOREIGN KEY (`covoiturage_id`) REFERENCES `covoiturage` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_D79F6B1171A51189` FOREIGN KEY (`passager_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `reservation`
--
ALTER TABLE `reservation`
  ADD CONSTRAINT `FK_42C8495558ABF955` FOREIGN KEY (`logement_id`) REFERENCES `logement` (`id`),
  ADD CONSTRAINT `FK_42C84955D8A38199` FOREIGN KEY (`locataire_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `reset_password_request`
--
ALTER TABLE `reset_password_request`
  ADD CONSTRAINT `FK_7CE748AA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
