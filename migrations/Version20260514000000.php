<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration to add ERP tables from Java application to Symfony
 */
final class Version20260514000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ERP tables (erp_matiere, erp_produit, erp_service, erp_achat, erp_vente) from Java application';
    }

    public function up(Schema $schema): void
    {
        // ERP Matiere (raw materials)
        $this->addSql('CREATE TABLE IF NOT EXISTS erp_matiere (
            id_matiere INT(11) NOT NULL AUTO_INCREMENT,
            nom VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            unite VARCHAR(50) NOT NULL DEFAULT \'unité\',
            stock DOUBLE NOT NULL DEFAULT 0,
            prix_unitaire DECIMAL(10,2) NOT NULL DEFAULT \'0.00\',
            seuil_critique DOUBLE NOT NULL DEFAULT 0,
            PRIMARY KEY (id_matiere)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // ERP Produit (finished products)
        $this->addSql('CREATE TABLE IF NOT EXISTS erp_produit (
            id_produit INT(11) NOT NULL AUTO_INCREMENT,
            nom VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            prix_vente DOUBLE NOT NULL DEFAULT 0,
            quantite_produite DOUBLE NOT NULL DEFAULT 1,
            stock DOUBLE NOT NULL DEFAULT 0,
            is_simple TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (id_produit)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // ERP Recette Ingredient (product recipe)
        $this->addSql('CREATE TABLE IF NOT EXISTS erp_recette_ingredient (
            id INT(11) NOT NULL AUTO_INCREMENT,
            id_produit INT(11) NOT NULL,
            id_matiere INT(11) NOT NULL,
            quantite DOUBLE NOT NULL DEFAULT 1,
            PRIMARY KEY (id),
            UNIQUE KEY uk_recette (id_produit, id_matiere),
            CONSTRAINT fk_recette_produit FOREIGN KEY (id_produit) REFERENCES erp_produit (id_produit) ON DELETE CASCADE,
            CONSTRAINT fk_recette_matiere FOREIGN KEY (id_matiere) REFERENCES erp_matiere (id_matiere) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // ERP Service
        $this->addSql('CREATE TABLE IF NOT EXISTS erp_service (
            id_service INT(11) NOT NULL AUTO_INCREMENT,
            nom VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            prix DOUBLE NOT NULL DEFAULT 0,
            stock INT(11) NOT NULL DEFAULT 0,
            seuil_critique INT(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (id_service),
            KEY idx_erp_service_stock (stock)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // ERP Achat (purchase orders)
        $this->addSql('CREATE TABLE IF NOT EXISTS erp_achat (
            id_achat INT(11) NOT NULL AUTO_INCREMENT,
            date_achat DATE NOT NULL,
            total DECIMAL(10,2) NOT NULL DEFAULT \'0.00\',
            paid TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (id_achat),
            KEY idx_erp_achat_date (date_achat)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // ERP Ligne Achat (purchase order lines)
        $this->addSql('CREATE TABLE IF NOT EXISTS erp_ligne_achat (
            id INT(11) NOT NULL AUTO_INCREMENT,
            id_achat INT(11) NOT NULL,
            id_matiere INT(11) NOT NULL,
            quantite DOUBLE NOT NULL DEFAULT 1,
            prix_unitaire DOUBLE NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            UNIQUE KEY uk_ligne_achat (id_achat, id_matiere),
            CONSTRAINT fk_lachat_achat FOREIGN KEY (id_achat) REFERENCES erp_achat (id_achat) ON DELETE CASCADE,
            CONSTRAINT fk_lachat_matiere FOREIGN KEY (id_matiere) REFERENCES erp_matiere (id_matiere) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // ERP Vente (sale orders)
        $this->addSql('CREATE TABLE IF NOT EXISTS erp_vente (
            id_vente INT(11) NOT NULL AUTO_INCREMENT,
            date_vente DATE NOT NULL,
            total DECIMAL(10,2) NOT NULL DEFAULT \'0.00\',
            PRIMARY KEY (id_vente)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // ERP Ligne Vente (sale order lines)
        $this->addSql('CREATE TABLE IF NOT EXISTS erp_ligne_vente (
            id INT(11) NOT NULL AUTO_INCREMENT,
            id_vente INT(11) NOT NULL,
            id_produit INT(11) NOT NULL,
            quantite INT(11) NOT NULL DEFAULT 1,
            prix_unitaire DOUBLE NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            CONSTRAINT fk_lvente_vente FOREIGN KEY (id_vente) REFERENCES erp_vente (id_vente) ON DELETE CASCADE,
            CONSTRAINT fk_lvente_produit FOREIGN KEY (id_produit) REFERENCES erp_produit (id_produit) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Add additional columns to existing tables if they don't exist
        $this->addSql('ALTER TABLE analyse 
            ADD COLUMN IF NOT EXISTS weather_data JSON DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS ai_diagnosis TEXT DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS confidence_score DECIMAL(5,2) DEFAULT NULL');

        $this->addSql('ALTER TABLE ferme 
            ADD COLUMN IF NOT EXISTS latitude DECIMAL(10,8) DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS longitude DECIMAL(11,8) DEFAULT NULL');

        $this->addSql('ALTER TABLE user 
            ADD COLUMN IF NOT EXISTS latitude DECIMAL(10,8) DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS longitude DECIMAL(11,8) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Remove ERP tables
        $this->addSql('DROP TABLE IF EXISTS erp_ligne_vente');
        $this->addSql('DROP TABLE IF EXISTS erp_vente');
        $this->addSql('DROP TABLE IF EXISTS erp_ligne_achat');
        $this->addSql('DROP TABLE IF EXISTS erp_achat');
        $this->addSql('DROP TABLE IF EXISTS erp_recette_ingredient');
        $this->addSql('DROP TABLE IF EXISTS erp_service');
        $this->addSql('DROP TABLE IF EXISTS erp_produit');
        $this->addSql('DROP TABLE IF EXISTS erp_matiere');

        // Remove additional columns
        $this->addSql('ALTER TABLE analyse 
            DROP COLUMN IF EXISTS weather_data,
            DROP COLUMN IF EXISTS ai_diagnosis,
            DROP COLUMN IF EXISTS confidence_score');

        $this->addSql('ALTER TABLE ferme 
            DROP COLUMN IF EXISTS latitude,
            DROP COLUMN IF EXISTS longitude');

        $this->addSql('ALTER TABLE user 
            DROP COLUMN IF EXISTS latitude,
            DROP COLUMN IF EXISTS longitude');
    }
}