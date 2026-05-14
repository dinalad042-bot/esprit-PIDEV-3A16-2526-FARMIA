-- FarmIA Database Backup
-- Generated: 2026-05-06 17:14:49
-- Database: farmia


CREATE TABLE `analyse` (
  `id_analyse` int(11) NOT NULL AUTO_INCREMENT,
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
  `id_technicien_id` int(11) DEFAULT NULL,
  `id_ferme_id` int(11) NOT NULL,
  `id_demandeur` int(11) NOT NULL,
  `id_animal_cible` int(11) DEFAULT NULL,
  `id_plante_cible` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_analyse`),
  KEY `IDX_351B0C7E33A2AC9B` (`id_technicien_id`),
  KEY `IDX_351B0C7E88D30FF2` (`id_ferme_id`),
  KEY `IDX_351B0C7EE6681A34` (`id_demandeur`),
  KEY `IDX_351B0C7EE36F40BD` (`id_animal_cible`),
  KEY `IDX_351B0C7EA7275E42` (`id_plante_cible`),
  CONSTRAINT `FK_351B0C7E4843FDA7` FOREIGN KEY (`id_ferme_id`) REFERENCES `ferme` (`id_ferme`) ON DELETE CASCADE,
  CONSTRAINT `FK_351B0C7EA7275E42` FOREIGN KEY (`id_plante_cible`) REFERENCES `plante` (`id_plante`) ON DELETE SET NULL,
  CONSTRAINT `FK_351B0C7EAD6DA333` FOREIGN KEY (`id_technicien_id`) REFERENCES `user` (`id_user`) ON DELETE SET NULL,
  CONSTRAINT `FK_351B0C7EE36F40BD` FOREIGN KEY (`id_animal_cible`) REFERENCES `animal` (`id_animal`) ON DELETE SET NULL,
  CONSTRAINT `FK_351B0C7EE6681A34` FOREIGN KEY (`id_demandeur`) REFERENCES `user` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `analyse` (`id_analyse`, `date_analyse`, `resultat_technique`, `image_url`, `weather_data`, `weather_fetched_at`, `statut`, `description_demande`, `ai_diagnosis_result`, `ai_diagnosis_date`, `ai_confidence_score`, `diagnosis_mode`, `id_technicien_id`, `id_ferme_id`, `id_demandeur`, `id_animal_cible`, `id_plante_cible`) VALUES ('2', '2026-05-06 16:46:00', 'Météo indisponible (clé api en cours d\'activation)', 'C:\\Users\\sliti\\Downloads\\sick-dying-plants-CC-Vicky-G.jpg', '{\"main\":{\"temp\":0,\"feels_like\":0,\"humidity\":0},\"weather\":[{\"description\":\"Ville non trouv\\u00e9e : \\\"dvsdv\\\". V\\u00e9rifiez l\'orthographe ou utilisez le code postal.\",\"icon\":\"01n\"}],\"wind\":{\"speed\":0},\"clouds\":{\"all\":0},\"error\":\"Ville non trouv\\u00e9e : \\\"dvsdv\\\". V\\u00e9rifiez l\'orthographe ou utilisez le code postal.\"}', '2026-05-06 16:54:57', 'en_cours', 'anlyser', '{
    \"condition\": \"Erreur de diagnostic\",
    \"symptoms\": [
        \"Réponse IA invalide\"
    ],
    \"treatment\": \"Veuillez réessayer ou consulter un expert.\",
    \"prevention\": \"\",
    \"urgency\": \"Surveiller\",
    \"needsExpert\": true,
    \"rawResponse\": \"Je suis prêt à t\'aider. Cependant, je n\'ai pas encore reçu les détails de l\'observation de terrain. Pourrais-tu me fournir les informations nécessaires pour que je puisse établir un diagnostic structuré ?\\n\\nUne fois que j\'aurai ces informations, je te répondrai sous forme de JSON avec la structure que tu as spécifiée. \\n\\nSi tu as déjà transmis les informations, je suis désolé de ne pas les avoir vues. Pourrais-tu les retransmettre ? \\n\\nSinon, voici un exemple de réponse en JSON pour que tu saches à quoi vous attendre:\\n\\n{\\n  \\\"condition\\\": \\\"\\\",\\n  \\\"confidence\\\": \\\"\\\",\\n  \\\"symptoms\\\": \\\"\\\",\\n  \\\"treatment\\\": \\\"\\\",\\n  \\\"prevention\\\": \\\"\\\",\\n  \\\"urgency\\\": \\\"\\\",\\n  \\\"needsExpertConsult\\\": ,\\n  \\\"rawResponse\\\": \\\"\\\"\\n}\"
}', '2026-05-06 16:54:57', 'LOW', 'text', '2', '1', '2', '1', NULL);

CREATE TABLE `animal` (
  `id_animal` int(11) NOT NULL AUTO_INCREMENT,
  `espece` varchar(255) NOT NULL,
  `etat_sante` varchar(255) NOT NULL,
  `date_naissance` date NOT NULL,
  `id_ferme` int(11) NOT NULL,
  PRIMARY KEY (`id_animal`),
  KEY `IDX_6AAB231F88D30FF2` (`id_ferme`),
  CONSTRAINT `FK_6AAB231F88D30FF2` FOREIGN KEY (`id_ferme`) REFERENCES `ferme` (`id_ferme`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `animal` (`id_animal`, `espece`, `etat_sante`, `date_naissance`, `id_ferme`) VALUES ('1', 'dvsdsvvs', 'sfksklf', '2026-04-30', '1');

CREATE TABLE `arrosage` (
  `id_arrosage` int(11) NOT NULL AUTO_INCREMENT,
  `date_arrosage` datetime NOT NULL,
  `id_plante` int(11) NOT NULL,
  PRIMARY KEY (`id_arrosage`),
  KEY `IDX_78E734CA774DDCAA` (`id_plante`),
  CONSTRAINT `FK_78E734CA774DDCAA` FOREIGN KEY (`id_plante`) REFERENCES `plante` (`id_plante`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `conseil` (
  `id_conseil` int(11) NOT NULL AUTO_INCREMENT,
  `description_conseil` longtext NOT NULL,
  `priorite` varchar(10) DEFAULT 'MOYENNE',
  `id_analyse` int(11) NOT NULL,
  PRIMARY KEY (`id_conseil`),
  KEY `IDX_3F3F0681EB86A50E` (`id_analyse`),
  CONSTRAINT `FK_3F3F0681EB86A50E` FOREIGN KEY (`id_analyse`) REFERENCES `analyse` (`id_analyse`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `erp_achat` (
  `id_achat` int(11) NOT NULL AUTO_INCREMENT,
  `date_achat` date NOT NULL,
  `total` double NOT NULL DEFAULT 0,
  `paid` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_achat`),
  KEY `idx_erp_achat_date` (`date_achat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `erp_ligne_achat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quantite` double NOT NULL,
  `prix_unitaire` double NOT NULL,
  `id_achat` int(11) NOT NULL,
  `id_matiere` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ligne_achat` (`id_achat`,`id_matiere`),
  KEY `IDX_17F2E914C82CC566` (`id_achat`),
  KEY `IDX_17F2E9144E89FE3A` (`id_matiere`),
  CONSTRAINT `FK_17F2E9144E89FE3A` FOREIGN KEY (`id_matiere`) REFERENCES `erp_matiere` (`id_matiere`),
  CONSTRAINT `FK_17F2E914C82CC566` FOREIGN KEY (`id_achat`) REFERENCES `erp_achat` (`id_achat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `erp_ligne_vente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quantite` int(11) NOT NULL,
  `prix_unitaire` double NOT NULL,
  `id_vente` int(11) NOT NULL,
  `id_produit` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_B9D1470E660F6B7C` (`id_vente`),
  KEY `IDX_B9D1470EF7384557` (`id_produit`),
  CONSTRAINT `FK_B9D1470E660F6B7C` FOREIGN KEY (`id_vente`) REFERENCES `erp_vente` (`id_vente`),
  CONSTRAINT `FK_B9D1470EF7384557` FOREIGN KEY (`id_produit`) REFERENCES `erp_produit` (`id_produit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `erp_liste_achat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quantite` int(11) NOT NULL,
  `prix_unitaire` double NOT NULL,
  `id_achat` int(11) NOT NULL,
  `id_service` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_erp_liste_achat` (`id_achat`,`id_service`),
  KEY `IDX_BA5D78AC82CC566` (`id_achat`),
  KEY `IDX_BA5D78A3F0033A2` (`id_service`),
  CONSTRAINT `FK_BA5D78A3F0033A2` FOREIGN KEY (`id_service`) REFERENCES `erp_service` (`id_service`),
  CONSTRAINT `FK_BA5D78AC82CC566` FOREIGN KEY (`id_achat`) REFERENCES `erp_achat` (`id_achat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `erp_liste_vente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quantite` int(11) NOT NULL,
  `prix_unitaire` double NOT NULL,
  `id_vente` int(11) NOT NULL,
  `id_service` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_A5867990660F6B7C` (`id_vente`),
  KEY `IDX_A58679903F0033A2` (`id_service`),
  CONSTRAINT `FK_A58679903F0033A2` FOREIGN KEY (`id_service`) REFERENCES `erp_service` (`id_service`),
  CONSTRAINT `FK_A5867990660F6B7C` FOREIGN KEY (`id_vente`) REFERENCES `erp_vente` (`id_vente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `erp_matiere` (
  `id_matiere` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `unite` varchar(50) NOT NULL DEFAULT 'unité',
  `stock` double NOT NULL DEFAULT 0,
  `prix_unitaire` double NOT NULL DEFAULT 0,
  `seuil_critique` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_matiere`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `erp_produit` (
  `id_produit` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `prix_vente` double NOT NULL DEFAULT 0,
  `quantite_produite` double NOT NULL DEFAULT 1,
  `stock` double NOT NULL DEFAULT 0,
  `is_simple` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_produit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `erp_recette_ingredient` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quantite` double NOT NULL,
  `id_produit` int(11) NOT NULL,
  `id_matiere` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_recette` (`id_produit`,`id_matiere`),
  KEY `IDX_CFB418ACF7384557` (`id_produit`),
  KEY `IDX_CFB418AC4E89FE3A` (`id_matiere`),
  CONSTRAINT `FK_CFB418AC4E89FE3A` FOREIGN KEY (`id_matiere`) REFERENCES `erp_matiere` (`id_matiere`),
  CONSTRAINT `FK_CFB418ACF7384557` FOREIGN KEY (`id_produit`) REFERENCES `erp_produit` (`id_produit`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `erp_service` (
  `id_service` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `prix` double NOT NULL DEFAULT 0,
  `stock` int(11) NOT NULL DEFAULT 0,
  `seuil_critique` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_service`),
  KEY `idx_erp_service_stock` (`stock`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `erp_vente` (
  `id_vente` int(11) NOT NULL AUTO_INCREMENT,
  `date_vente` date NOT NULL,
  `total` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_vente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `ferme` (
  `id_ferme` int(11) NOT NULL AUTO_INCREMENT,
  `nom_ferme` varchar(255) NOT NULL,
  `lieu` varchar(255) NOT NULL,
  `surface` double DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_ferme`),
  KEY `IDX_66564EC26B3CA4B` (`id_user`),
  CONSTRAINT `FK_66564EC26B3CA4B` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `ferme` (`id_ferme`, `nom_ferme`, `lieu`, `surface`, `latitude`, `longitude`, `created_at`, `updated_at`, `id_user`) VALUES ('1', 'olo', 'dvsdv', '5', '3.35', '5.23', '2026-05-05 14:59:43', '2026-05-05 14:59:43', '1');
INSERT INTO `ferme` (`id_ferme`, `nom_ferme`, `lieu`, `surface`, `latitude`, `longitude`, `created_at`, `updated_at`, `id_user`) VALUES ('2', 'ZZZ', 'dvsdv', '5', '3.35', '5.23', '2026-05-05 14:59:58', '2026-05-05 14:59:58', '1');

CREATE TABLE `messenger_messages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `body` longtext NOT NULL,
  `headers` longtext NOT NULL,
  `queue_name` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL,
  `available_at` datetime NOT NULL,
  `delivered_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750` (`queue_name`,`available_at`,`delivered_at`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `notification` (
  `id_notification` int(11) NOT NULL AUTO_INCREMENT,
  `message` longtext NOT NULL,
  `type` varchar(50) NOT NULL,
  `is_read` tinyint(1) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `id_user` int(11) NOT NULL,
  PRIMARY KEY (`id_notification`),
  KEY `IDX_BF5476CA6B3CA4B` (`id_user`),
  CONSTRAINT `FK_BF5476CA6B3CA4B` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `plante` (
  `id_plante` int(11) NOT NULL AUTO_INCREMENT,
  `nom_espece` varchar(255) NOT NULL,
  `cycle_vie` varchar(255) NOT NULL,
  `quantite` int(11) NOT NULL,
  `id_ferme` int(11) NOT NULL,
  PRIMARY KEY (`id_plante`),
  KEY `IDX_517A694788D30FF2` (`id_ferme`),
  CONSTRAINT `FK_517A694788D30FF2` FOREIGN KEY (`id_ferme`) REFERENCES `ferme` (`id_ferme`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `plante` (`id_plante`, `nom_espece`, `cycle_vie`, `quantite`, `id_ferme`) VALUES ('1', 'svsdvsd', '15', '1', '1');

CREATE TABLE `suivi_sante` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_consultation` datetime NOT NULL,
  `diagnostic` longtext NOT NULL,
  `etat_au_moment` varchar(50) DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL,
  `animal_id` int(11) NOT NULL,
  `performed_by_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_6C0412484C9C96F2` (`animal_id`),
  KEY `FK_6C0412482E65C292` (`performed_by_id`),
  CONSTRAINT `FK_6C0412482E65C292` FOREIGN KEY (`performed_by_id`) REFERENCES `user` (`id_user`),
  CONSTRAINT `FK_6C0412488E962C16` FOREIGN KEY (`animal_id`) REFERENCES `animal` (`id_animal`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `user` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
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
  `reset_code_expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`),
  UNIQUE KEY `UNIQ_8D93D649ABE530DA` (`cin`),
  UNIQUE KEY `UNIQ_8D93D649450FF010` (`telephone`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `user` (`id_user`, `nom`, `prenom`, `email`, `password`, `cin`, `adresse`, `latitude`, `longitude`, `telephone`, `image_url`, `role`, `created_at`, `updated_at`, `reset_code`, `reset_code_expires_at`) VALUES ('1', 'ALADIN', 'SLITI', 'exact.clownfish.hptc@hidingmail.net', '343119613b3cadcd$b931ebd728cd9a880c3ca572263cc16313e16288e772c571a328fe0fd68bec24', '55566121', 'exact.clownfish.hptc@hidingmail.net', NULL, NULL, '52394602', NULL, 'AGRICOLE', '2026-05-05 14:58:53', '2026-05-05 14:58:53', NULL, NULL);
INSERT INTO `user` (`id_user`, `nom`, `prenom`, `email`, `password`, `cin`, `adresse`, `latitude`, `longitude`, `telephone`, `image_url`, `role`, `created_at`, `updated_at`, `reset_code`, `reset_code_expires_at`) VALUES ('2', 'ALADIN', 'SLITI', 'helacitu@denipl.com', '9c8da645afb06676$75a940823bb176d64f06bcffd03af09efe7ba61a367479706012b29efc2ef903', '55522266', 'exact.clownfish.hptc@hidingmail.net', NULL, NULL, '52395602', NULL, 'EXPERT', '2026-05-06 14:19:38', '2026-05-06 14:19:38', NULL, NULL);

CREATE TABLE `user_face` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `image_path` varchar(500) DEFAULT NULL,
  `samples_count` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `confidence_score` double DEFAULT NULL,
  `enrolled_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_7F9537AEA76ED395` (`user_id`),
  CONSTRAINT `FK_7F9537AEA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `user_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `action_type` varchar(20) DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `performed_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_6429094EA76ED395` (`user_id`),
  KEY `IDX_6429094E99EB8EA2` (`performed_by`),
  CONSTRAINT `FK_6429094E99EB8EA2` FOREIGN KEY (`performed_by`) REFERENCES `user` (`id_user`) ON DELETE SET NULL,
  CONSTRAINT `FK_6429094EA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `user_log` (`id`, `action_type`, `timestamp`, `description`, `user_id`, `performed_by`) VALUES ('1', 'SIGNUP_WEB', '2026-05-05 14:58:53', 'SUCCESS', '1', NULL);
INSERT INTO `user_log` (`id`, `action_type`, `timestamp`, `description`, `user_id`, `performed_by`) VALUES ('2', 'LOGIN', '2026-05-05 14:59:20', 'SUCCESS', '1', '1');
INSERT INTO `user_log` (`id`, `action_type`, `timestamp`, `description`, `user_id`, `performed_by`) VALUES ('3', 'LOGIN', '2026-05-06 12:21:23', 'SUCCESS', '1', '1');
INSERT INTO `user_log` (`id`, `action_type`, `timestamp`, `description`, `user_id`, `performed_by`) VALUES ('4', 'SIGNUP_WEB', '2026-05-06 14:19:38', 'SUCCESS', '2', NULL);
INSERT INTO `user_log` (`id`, `action_type`, `timestamp`, `description`, `user_id`, `performed_by`) VALUES ('5', 'LOGIN', '2026-05-06 14:19:52', 'SUCCESS', '2', '2');
INSERT INTO `user_log` (`id`, `action_type`, `timestamp`, `description`, `user_id`, `performed_by`) VALUES ('6', 'LOGIN', '2026-05-06 15:55:11', 'SUCCESS', '2', '2');
INSERT INTO `user_log` (`id`, `action_type`, `timestamp`, `description`, `user_id`, `performed_by`) VALUES ('7', 'LOGIN', '2026-05-06 15:00:12', 'SUCCESS', '2', '2');
INSERT INTO `user_log` (`id`, `action_type`, `timestamp`, `description`, `user_id`, `performed_by`) VALUES ('8', 'LOGIN', '2026-05-06 15:06:55', 'SUCCESS', '2', '2');
INSERT INTO `user_log` (`id`, `action_type`, `timestamp`, `description`, `user_id`, `performed_by`) VALUES ('9', 'LOGIN', '2026-05-06 15:07:24', 'SUCCESS', '1', '1');
