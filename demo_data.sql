-- =====================================================
-- FARMIA DEMO DATA
-- =====================================================
-- Run this SQL to populate your database with test data
-- Command: mysql -u root farmai < demo_data.sql
-- =====================================================

USE farmai;

-- Clear existing data (optional - remove if you want to keep existing data)
-- SET FOREIGN_KEY_CHECKS = 0;
-- TRUNCATE TABLE conseil;
-- TRUNCATE TABLE analyse;
-- TRUNCATE TABLE animal;
-- TRUNCATE TABLE plante;
-- TRUNCATE TABLE ferme;
-- TRUNCATE TABLE user;
-- SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- 1. USERS (Mot de passe: "password" haché)
-- =====================================================
INSERT INTO `user` (`nom`, `prenom`, `email`, `password`, `cin`, `adresse`, `telephone`, `role`, `created_at`, `updated_at`) VALUES
('Ben Ali', 'Mohamed', 'admin@farmia.tn', '$2y$13$9y8T9fL7bQ3vX5mN4pK2dO1hG6jF8eA0sD4gH7jK3lP6nB9mV2cX4zA1qW5eR8tY0uI3oP6lK9jH2gF5dS8aQ1wE4rT7yU0iO3pL6kJ9hG2fD5sA8qW1eR4tY7', '12345678', 'Ariana, Tunisie', '20123456', 'ADMIN', NOW(), NOW()),
('Trabelsi', 'Fatma', 'fatma.fermier@farmia.tn', '$2y$13$9y8T9fL7bQ3vX5mN4pK2dO1hG6jF8eA0sD4gH7jK3lP6nB9mV2cX4zA1qW5eR8tY0uI3oP6lK9jH2gF5dS8aQ1wE4rT7yU0iO3pL6kJ9hG2fD5sA8qW1eR4tY7', '87654321', 'Sidi Bouzid, Tunisie', '20789012', 'AGRICULTEUR', NOW(), NOW()),
('Gharbi', 'Ali', 'ali.tech@farmia.tn', '$2y$13$9y8T9fL7bQ3vX5mN4pK2dO1hG6jF8eA0sD4gH7jK3lP6nB9mV2cX4zA1qW5eR8tY0uI3oP6lK9jH2gF5dS8aQ1wE4rT7yU0iO3pL6kJ9hG2fD5sA8qW1eR4tY7', '45678912', 'Sousse, Tunisie', '55342189', 'TECHNICIEN', NOW(), NOW()),
('Khadhraoui', 'Sonia', 'sonia.expert@farmia.tn', '$2y$13$9y8T9fL7bQ3vX5mN4pK2dO1hG6jF8eA0sD4gH7jK3lP6nB9mV2cX4zA1qW5eR8tY0uI3oP6lK9jH2gF5dS8aQ1wE4rT7yU0iO3pL6kJ9hG2fD5sA8qW1eR4tY7', '78912345', 'Nabeul, Tunisie', '98234167', 'EXPERT', NOW(), NOW()),
('Jebali', 'Karim', 'karim@farmia.tn', '$2y$13$9y8T9fL7bQ3vX5mN4pK2dO1hG6jF8eA0sD4gH7jK3lP6nB9mV2cX4zA1qW5eR8tY0uI3oP6lK9jH2gF5dS8aQ1wE4rT7yU0iO3pL6kJ9hG2fD5sA8qW1eR4tY7', '32165498', 'Kairouan, Tunisie', '44127890', 'AGRICULTEUR', NOW(), NOW());

-- =====================================================
-- 2. FERMES (Farms)
-- =====================================================
INSERT INTO `ferme` (`nom_ferme`, `lieu`, `surface`, `latitude`, `longitude`, `created_at`, `updated_at`, `id_user`) VALUES
('Ferme Ben Ali', 'Ariana, Tunisie', 50.5, 36.8458, 10.1936, NOW(), NOW(), 2),
('Domaine Trabelsi', 'Sidi Bouzid Centre', 120.0, 35.0382, 9.4854, NOW(), NOW(), 2),
('Ferme Agricole Gharbi', 'Sousse Nord', 75.25, 35.8288, 10.6406, NOW(), NOW(), 5),
('Vergers Khadhraoui', 'Nabeul Sud', 45.0, 36.4560, 10.7376, NOW(), NOW(), 4),
('Ferme Collective du Centre', 'Kairouan Ouest', 200.0, 35.6784, 10.0963, NOW(), NOW(), 5);

-- =====================================================
-- 3. ANIMAUX (Animals)
-- =====================================================
INSERT INTO `animal` (`espece`, `etat_sante`, `date_naissance`, `id_ferme`) VALUES
('Vache Holstein', 'BONNE', '2022-03-15', 1),
('Mouton Barbarin', 'EXCELLENTE', '2023-01-20', 1),
('Chèvre Alpine', 'MOYENNE', '2022-07-10', 2),
('Poulet de Chair', 'BONNE', '2024-01-05', 2),
('Dinde locale', 'EXCELLENTE', '2023-06-18', 3),
('Vache Jersey', 'BONNE', '2021-11-30', 3),
('Mouton Queue Fine', 'MOYENNE', '2022-09-22', 4),
('Cheval Arabe', 'EXCELLENTE', '2019-04-12', 4),
('Âne', 'BONNE', '2020-08-05', 5),
('Chèvre Saanen', 'EXCELLENTE', '2023-03-25', 5);

-- =====================================================
-- 4. PLANTES (Plants/Crops)
-- =====================================================
INSERT INTO `plante` (`nom_espece`, `cycle_vie`, `id_ferme`, `quantite`) VALUES
('Blé Dur', 'ANNUEL', 1, 500),
('Orge', 'ANNUEL', 1, 300),
('Olivier', 'PERENNE', 2, 150),
('Pomme de Terre', 'ANNUEL', 2, 1000),
('Tomate', 'ANNUEL', 3, 250),
('Poivron', 'ANNUEL', 3, 180),
('Agrumes (Orange)', 'PERENNE', 4, 80),
('Fraise', 'ANNUEL', 4, 120),
('Figuier', 'PERENNE', 5, 60),
('Cactus (Figuier de Barbarie)', 'PERENNE', 5, 200),
('Blé Tendre', 'ANNUEL', 1, 400),
('Avoine', 'ANNUEL', 2, 250),
('Lentille', 'ANNUEL', 5, 150);

-- =====================================================
-- 5. ANALYSES (Analyses - linked to technicians and farms)
-- =====================================================
INSERT INTO `analyse` (`date_analyse`, `resultat_technique`, `image_url`, `id_technicien`, `id_ferme`) VALUES
(NOW(), '{"ph": 6.5, "nutriments": {"azote": "adequat", "phosphore": "faible", "potassium": "adequat"}, "humidite": "45%", "recommandation": "Ajouter engrais phosphaté"}', 'uploads/analyses/analyse_1.jpg', 3, 1),
(DATE_SUB(NOW(), INTERVAL 2 DAY), '{"ph": 7.2, "nutriments": {"azote": "faible", "phosphore": "adequat", "potassium": "eleve"}, "humidite": "38%", "recommandation": "Irrigation recommandée"}', 'uploads/analyses/analyse_2.jpg', 3, 2),
(DATE_SUB(NOW(), INTERVAL 5 DAY), '{"ph": 6.8, "nutriments": {"azote": "eleve", "phosphore": "adequat", "potassium": "adequat"}, "humidite": "52%", "recommandation": "Conditions optimales"}', 'uploads/analyses/analyse_3.jpg', 3, 3);

-- =====================================================
-- 6. CONSEILS (Advice/Recommendations - linked to analyses)
-- =====================================================
INSERT INTO `conseil` (`description_conseil`, `priorite`, `id_analyse`) VALUES
('Appliquer un engrais phosphaté pour compenser le manque de phosphore. Dose recommandée: 150kg/ha.', 'HAUTE', 1),
('Augmenter la fréquence d irrigation. Arroser tôt le matin pour minimiser l évaporation.', 'MOYENNE', 2),
('Maintenir les pratiques actuelles. Surveillance régulière recommandée.', 'BASSE', 3),
('Prévoir une analyse complémentaire dans 15 jours pour suivre l évolution.', 'MOYENNE', 1);

-- =====================================================
-- DATA INSERTED SUCCESSFULLY
-- =====================================================
-- Summary:
-- - 5 Users (admin, 2 agriculteurs, 1 technicien, 1 expert)
-- - 5 Fermes (Farms)
-- - 10 Animaux (Animals)
-- - 13 Plantes (Plants/Crops)
-- - 3 Analyses
-- - 4 Conseils (Recommendations)
-- =====================================================

SELECT 'Demo data inserted successfully!' AS Status;
SELECT CONCAT((SELECT COUNT(*) FROM user), ' users created') AS Users;
SELECT CONCAT((SELECT COUNT(*) FROM ferme), ' farms created') AS Farms;
SELECT CONCAT((SELECT COUNT(*) FROM animal), ' animals created') AS Animals;
SELECT CONCAT((SELECT COUNT(*) FROM plante), ' plants created') AS Plants;
SELECT CONCAT((SELECT COUNT(*) FROM analyse), ' analyses created') AS Analyses;
SELECT CONCAT((SELECT COUNT(*) FROM conseil), ' conseils created') AS Conseils;
