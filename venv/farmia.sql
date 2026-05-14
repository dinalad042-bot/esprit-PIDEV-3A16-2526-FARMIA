-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 06, 2026 at 10:46 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `farmia`
--

-- --------------------------------------------------------

--
-- Table structure for table `analyse`
--

CREATE TABLE `analyse` (
  `id_analyse` int(11) NOT NULL,
  `date_analyse` datetime DEFAULT NULL,
  `resultat_technique` longtext DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `weather_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`weather_data`)),
  `weather_fetched_at` datetime DEFAULT NULL,
  `statut` varchar(20) NOT NULL,
  `description_demande` longtext DEFAULT NULL,
  `ai_diagnosis_result` longtext DEFAULT NULL,
  `ai_diagnosis_date` datetime DEFAULT NULL,
  `ai_confidence_score` varchar(20) DEFAULT NULL,
  `diagnosis_mode` varchar(20) DEFAULT NULL,
  `id_technicien` int(11) DEFAULT NULL,
  `id_ferme` int(11) NOT NULL,
  `id_demandeur` int(11) NOT NULL,
  `id_animal_cible` int(11) DEFAULT NULL,
  `id_plante_cible` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `animal`
--

CREATE TABLE `animal` (
  `id_animal` int(11) NOT NULL,
  `espece` varchar(255) NOT NULL,
  `etat_sante` varchar(255) NOT NULL,
  `date_naissance` date NOT NULL,
  `id_ferme` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `arrosage`
--

CREATE TABLE `arrosage` (
  `id_arrosage` int(11) NOT NULL,
  `date_arrosage` datetime NOT NULL,
  `id_plante` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conseil`
--

CREATE TABLE `conseil` (
  `id_conseil` int(11) NOT NULL,
  `description_conseil` longtext NOT NULL,
  `priorite` varchar(10) DEFAULT 'MOYENNE',
  `id_analyse` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `erp_achat`
--

CREATE TABLE `erp_achat` (
  `id_achat` int(11) NOT NULL,
  `date_achat` date NOT NULL,
  `total` double NOT NULL DEFAULT 0,
  `paid` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `erp_ligne_achat`
--

CREATE TABLE `erp_ligne_achat` (
  `id` int(11) NOT NULL,
  `quantite` double NOT NULL,
  `prix_unitaire` double NOT NULL,
  `id_achat` int(11) NOT NULL,
  `id_matiere` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `erp_ligne_vente`
--

CREATE TABLE `erp_ligne_vente` (
  `id` int(11) NOT NULL,
  `quantite` int(11) NOT NULL,
  `prix_unitaire` double NOT NULL,
  `id_vente` int(11) NOT NULL,
  `id_produit` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `erp_liste_achat`
--

CREATE TABLE `erp_liste_achat` (
  `id` int(11) NOT NULL,
  `quantite` int(11) NOT NULL,
  `prix_unitaire` double NOT NULL,
  `id_achat` int(11) NOT NULL,
  `id_service` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `erp_liste_vente`
--

CREATE TABLE `erp_liste_vente` (
  `id` int(11) NOT NULL,
  `quantite` int(11) NOT NULL,
  `prix_unitaire` double NOT NULL,
  `id_vente` int(11) NOT NULL,
  `id_service` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `erp_matiere`
--

CREATE TABLE `erp_matiere` (
  `id_matiere` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `unite` varchar(50) NOT NULL DEFAULT 'unité',
  `stock` double NOT NULL DEFAULT 0,
  `prix_unitaire` double NOT NULL DEFAULT 0,
  `seuil_critique` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `erp_produit`
--

CREATE TABLE `erp_produit` (
  `id_produit` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `prix_vente` double NOT NULL DEFAULT 0,
  `quantite_produite` double NOT NULL DEFAULT 1,
  `stock` double NOT NULL DEFAULT 0,
  `is_simple` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `erp_recette_ingredient`
--

CREATE TABLE `erp_recette_ingredient` (
  `id` int(11) NOT NULL,
  `quantite` double NOT NULL,
  `id_produit` int(11) NOT NULL,
  `id_matiere` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `erp_service`
--

CREATE TABLE `erp_service` (
  `id_service` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `prix` double NOT NULL DEFAULT 0,
  `stock` int(11) NOT NULL DEFAULT 0,
  `seuil_critique` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `erp_vente`
--

CREATE TABLE `erp_vente` (
  `id_vente` int(11) NOT NULL,
  `date_vente` date NOT NULL,
  `total` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ferme`
--

CREATE TABLE `ferme` (
  `id_ferme` int(11) NOT NULL,
  `nom_ferme` varchar(255) NOT NULL,
  `lieu` varchar(255) NOT NULL,
  `surface` double DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ferme`
--

INSERT INTO `ferme` (`id_ferme`, `nom_ferme`, `lieu`, `surface`, `latitude`, `longitude`, `created_at`, `updated_at`, `id_user`) VALUES
(1, 'olo', 'dvsdv', 5, 3.35, 5.23, '2026-05-05 14:59:43', '2026-05-05 14:59:43', 1),
(2, 'ZZZ', 'dvsdv', 5, 3.35, 5.23, '2026-05-05 14:59:58', '2026-05-05 14:59:58', 1);

-- --------------------------------------------------------

--
-- Table structure for table `messenger_messages`
--

CREATE TABLE `messenger_messages` (
  `id` bigint(20) NOT NULL,
  `body` longtext NOT NULL,
  `headers` longtext NOT NULL,
  `queue_name` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL,
  `available_at` datetime NOT NULL,
  `delivered_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `id_notification` int(11) NOT NULL,
  `message` longtext NOT NULL,
  `type` varchar(50) NOT NULL,
  `is_read` tinyint(1) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `id_user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `plante`
--

CREATE TABLE `plante` (
  `id_plante` int(11) NOT NULL,
  `nom_espece` varchar(255) NOT NULL,
  `cycle_vie` varchar(255) NOT NULL,
  `quantite` int(11) NOT NULL,
  `id_ferme` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suivi_sante`
--

CREATE TABLE `suivi_sante` (
  `id` int(11) NOT NULL,
  `date_consultation` datetime NOT NULL,
  `diagnostic` longtext NOT NULL,
  `etat_au_moment` varchar(50) DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL,
  `id_animal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `cin` varchar(8) DEFAULT NULL,
  `adresse` longtext DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `telephone` varchar(8) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `reset_code` varchar(6) DEFAULT NULL,
  `reset_code_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id_user`, `nom`, `prenom`, `email`, `password`, `cin`, `adresse`, `latitude`, `longitude`, `telephone`, `image_url`, `role`, `created_at`, `updated_at`, `reset_code`, `reset_code_expires_at`) VALUES
(1, 'ALADIN', 'SLITI', 'exact.clownfish.hptc@hidingmail.net', '343119613b3cadcd$b931ebd728cd9a880c3ca572263cc16313e16288e772c571a328fe0fd68bec24', '55566121', 'exact.clownfish.hptc@hidingmail.net', NULL, NULL, '52394602', NULL, 'AGRICOLE', '2026-05-05 14:58:53', '2026-05-05 14:58:53', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_face`
--

CREATE TABLE `user_face` (
  `id` int(11) NOT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `samples_count` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `confidence_score` double DEFAULT NULL,
  `enrolled_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_log`
--

CREATE TABLE `user_log` (
  `id` bigint(20) NOT NULL,
  `action_type` varchar(20) DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `performed_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_log`
--

INSERT INTO `user_log` (`id`, `action_type`, `timestamp`, `description`, `user_id`, `performed_by`) VALUES
(1, 'SIGNUP_WEB', '2026-05-05 14:58:53', 'SUCCESS', 1, NULL),
(2, 'LOGIN', '2026-05-05 14:59:20', 'SUCCESS', 1, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `analyse`
--
ALTER TABLE `analyse`
  ADD PRIMARY KEY (`id_analyse`),
  ADD KEY `IDX_351B0C7E33A2AC9B` (`id_technicien`),
  ADD KEY `IDX_351B0C7E88D30FF2` (`id_ferme`),
  ADD KEY `IDX_351B0C7EE6681A34` (`id_demandeur`),
  ADD KEY `IDX_351B0C7EE36F40BD` (`id_animal_cible`),
  ADD KEY `IDX_351B0C7EA7275E42` (`id_plante_cible`);

--
-- Indexes for table `animal`
--
ALTER TABLE `animal`
  ADD PRIMARY KEY (`id_animal`),
  ADD KEY `IDX_6AAB231F88D30FF2` (`id_ferme`);

--
-- Indexes for table `arrosage`
--
ALTER TABLE `arrosage`
  ADD PRIMARY KEY (`id_arrosage`),
  ADD KEY `IDX_78E734CA774DDCAA` (`id_plante`);

--
-- Indexes for table `conseil`
--
ALTER TABLE `conseil`
  ADD PRIMARY KEY (`id_conseil`),
  ADD KEY `IDX_3F3F0681EB86A50E` (`id_analyse`);

--
-- Indexes for table `erp_achat`
--
ALTER TABLE `erp_achat`
  ADD PRIMARY KEY (`id_achat`),
  ADD KEY `idx_erp_achat_date` (`date_achat`);

--
-- Indexes for table `erp_ligne_achat`
--
ALTER TABLE `erp_ligne_achat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_ligne_achat` (`id_achat`,`id_matiere`),
  ADD KEY `IDX_17F2E914C82CC566` (`id_achat`),
  ADD KEY `IDX_17F2E9144E89FE3A` (`id_matiere`);

--
-- Indexes for table `erp_ligne_vente`
--
ALTER TABLE `erp_ligne_vente`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_B9D1470E660F6B7C` (`id_vente`),
  ADD KEY `IDX_B9D1470EF7384557` (`id_produit`);

--
-- Indexes for table `erp_liste_achat`
--
ALTER TABLE `erp_liste_achat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_erp_liste_achat` (`id_achat`,`id_service`),
  ADD KEY `IDX_BA5D78AC82CC566` (`id_achat`),
  ADD KEY `IDX_BA5D78A3F0033A2` (`id_service`);

--
-- Indexes for table `erp_liste_vente`
--
ALTER TABLE `erp_liste_vente`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_A5867990660F6B7C` (`id_vente`),
  ADD KEY `IDX_A58679903F0033A2` (`id_service`);

--
-- Indexes for table `erp_matiere`
--
ALTER TABLE `erp_matiere`
  ADD PRIMARY KEY (`id_matiere`);

--
-- Indexes for table `erp_produit`
--
ALTER TABLE `erp_produit`
  ADD PRIMARY KEY (`id_produit`);

--
-- Indexes for table `erp_recette_ingredient`
--
ALTER TABLE `erp_recette_ingredient`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_recette` (`id_produit`,`id_matiere`),
  ADD KEY `IDX_CFB418ACF7384557` (`id_produit`),
  ADD KEY `IDX_CFB418AC4E89FE3A` (`id_matiere`);

--
-- Indexes for table `erp_service`
--
ALTER TABLE `erp_service`
  ADD PRIMARY KEY (`id_service`),
  ADD KEY `idx_erp_service_stock` (`stock`);

--
-- Indexes for table `erp_vente`
--
ALTER TABLE `erp_vente`
  ADD PRIMARY KEY (`id_vente`);

--
-- Indexes for table `ferme`
--
ALTER TABLE `ferme`
  ADD PRIMARY KEY (`id_ferme`),
  ADD KEY `IDX_66564EC26B3CA4B` (`id_user`);

--
-- Indexes for table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750` (`queue_name`,`available_at`,`delivered_at`,`id`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`id_notification`),
  ADD KEY `IDX_BF5476CA6B3CA4B` (`id_user`);

--
-- Indexes for table `plante`
--
ALTER TABLE `plante`
  ADD PRIMARY KEY (`id_plante`),
  ADD KEY `IDX_517A694788D30FF2` (`id_ferme`);

--
-- Indexes for table `suivi_sante`
--
ALTER TABLE `suivi_sante`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_6C0412484C9C96F2` (`id_animal`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`),
  ADD UNIQUE KEY `UNIQ_8D93D649ABE530DA` (`cin`),
  ADD UNIQUE KEY `UNIQ_8D93D649450FF010` (`telephone`);

--
-- Indexes for table `user_face`
--
ALTER TABLE `user_face`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_7F9537AEA76ED395` (`user_id`);

--
-- Indexes for table `user_log`
--
ALTER TABLE `user_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_6429094EA76ED395` (`user_id`),
  ADD KEY `IDX_6429094E99EB8EA2` (`performed_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `analyse`
--
ALTER TABLE `analyse`
  MODIFY `id_analyse` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `animal`
--
ALTER TABLE `animal`
  MODIFY `id_animal` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `arrosage`
--
ALTER TABLE `arrosage`
  MODIFY `id_arrosage` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conseil`
--
ALTER TABLE `conseil`
  MODIFY `id_conseil` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erp_achat`
--
ALTER TABLE `erp_achat`
  MODIFY `id_achat` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erp_ligne_achat`
--
ALTER TABLE `erp_ligne_achat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erp_ligne_vente`
--
ALTER TABLE `erp_ligne_vente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erp_liste_achat`
--
ALTER TABLE `erp_liste_achat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erp_liste_vente`
--
ALTER TABLE `erp_liste_vente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erp_matiere`
--
ALTER TABLE `erp_matiere`
  MODIFY `id_matiere` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erp_produit`
--
ALTER TABLE `erp_produit`
  MODIFY `id_produit` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erp_recette_ingredient`
--
ALTER TABLE `erp_recette_ingredient`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erp_service`
--
ALTER TABLE `erp_service`
  MODIFY `id_service` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `erp_vente`
--
ALTER TABLE `erp_vente`
  MODIFY `id_vente` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ferme`
--
ALTER TABLE `ferme`
  MODIFY `id_ferme` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `id_notification` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `plante`
--
ALTER TABLE `plante`
  MODIFY `id_plante` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suivi_sante`
--
ALTER TABLE `suivi_sante`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_face`
--
ALTER TABLE `user_face`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_log`
--
ALTER TABLE `user_log`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `analyse`
--
ALTER TABLE `analyse`
  ADD CONSTRAINT `FK_351B0C7E33A2AC9B` FOREIGN KEY (`id_technicien`) REFERENCES `user` (`id_user`) ON DELETE SET NULL,
  ADD CONSTRAINT `FK_351B0C7E88D30FF2` FOREIGN KEY (`id_ferme`) REFERENCES `ferme` (`id_ferme`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_351B0C7EA7275E42` FOREIGN KEY (`id_plante_cible`) REFERENCES `plante` (`id_plante`) ON DELETE SET NULL,
  ADD CONSTRAINT `FK_351B0C7EE36F40BD` FOREIGN KEY (`id_animal_cible`) REFERENCES `animal` (`id_animal`) ON DELETE SET NULL,
  ADD CONSTRAINT `FK_351B0C7EE6681A34` FOREIGN KEY (`id_demandeur`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `animal`
--
ALTER TABLE `animal`
  ADD CONSTRAINT `FK_6AAB231F88D30FF2` FOREIGN KEY (`id_ferme`) REFERENCES `ferme` (`id_ferme`);

--
-- Constraints for table `arrosage`
--
ALTER TABLE `arrosage`
  ADD CONSTRAINT `FK_78E734CA774DDCAA` FOREIGN KEY (`id_plante`) REFERENCES `plante` (`id_plante`);

--
-- Constraints for table `conseil`
--
ALTER TABLE `conseil`
  ADD CONSTRAINT `FK_3F3F0681EB86A50E` FOREIGN KEY (`id_analyse`) REFERENCES `analyse` (`id_analyse`) ON DELETE CASCADE;

--
-- Constraints for table `erp_ligne_achat`
--
ALTER TABLE `erp_ligne_achat`
  ADD CONSTRAINT `FK_17F2E9144E89FE3A` FOREIGN KEY (`id_matiere`) REFERENCES `erp_matiere` (`id_matiere`),
  ADD CONSTRAINT `FK_17F2E914C82CC566` FOREIGN KEY (`id_achat`) REFERENCES `erp_achat` (`id_achat`);

--
-- Constraints for table `erp_ligne_vente`
--
ALTER TABLE `erp_ligne_vente`
  ADD CONSTRAINT `FK_B9D1470E660F6B7C` FOREIGN KEY (`id_vente`) REFERENCES `erp_vente` (`id_vente`),
  ADD CONSTRAINT `FK_B9D1470EF7384557` FOREIGN KEY (`id_produit`) REFERENCES `erp_produit` (`id_produit`);

--
-- Constraints for table `erp_liste_achat`
--
ALTER TABLE `erp_liste_achat`
  ADD CONSTRAINT `FK_BA5D78A3F0033A2` FOREIGN KEY (`id_service`) REFERENCES `erp_service` (`id_service`),
  ADD CONSTRAINT `FK_BA5D78AC82CC566` FOREIGN KEY (`id_achat`) REFERENCES `erp_achat` (`id_achat`);

--
-- Constraints for table `erp_liste_vente`
--
ALTER TABLE `erp_liste_vente`
  ADD CONSTRAINT `FK_A58679903F0033A2` FOREIGN KEY (`id_service`) REFERENCES `erp_service` (`id_service`),
  ADD CONSTRAINT `FK_A5867990660F6B7C` FOREIGN KEY (`id_vente`) REFERENCES `erp_vente` (`id_vente`);

--
-- Constraints for table `erp_recette_ingredient`
--
ALTER TABLE `erp_recette_ingredient`
  ADD CONSTRAINT `FK_CFB418AC4E89FE3A` FOREIGN KEY (`id_matiere`) REFERENCES `erp_matiere` (`id_matiere`),
  ADD CONSTRAINT `FK_CFB418ACF7384557` FOREIGN KEY (`id_produit`) REFERENCES `erp_produit` (`id_produit`) ON DELETE CASCADE;

--
-- Constraints for table `ferme`
--
ALTER TABLE `ferme`
  ADD CONSTRAINT `FK_66564EC26B3CA4B` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE SET NULL;

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `FK_BF5476CA6B3CA4B` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `plante`
--
ALTER TABLE `plante`
  ADD CONSTRAINT `FK_517A694788D30FF2` FOREIGN KEY (`id_ferme`) REFERENCES `ferme` (`id_ferme`);

--
-- Constraints for table `suivi_sante`
--
ALTER TABLE `suivi_sante`
  ADD CONSTRAINT `FK_6C0412484C9C96F2` FOREIGN KEY (`id_animal`) REFERENCES `animal` (`id_animal`) ON DELETE CASCADE;

--
-- Constraints for table `user_face`
--
ALTER TABLE `user_face`
  ADD CONSTRAINT `FK_7F9537AEA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `user_log`
--
ALTER TABLE `user_log`
  ADD CONSTRAINT `FK_6429094E99EB8EA2` FOREIGN KEY (`performed_by`) REFERENCES `user` (`id_user`) ON DELETE SET NULL,
  ADD CONSTRAINT `FK_6429094EA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id_user`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
