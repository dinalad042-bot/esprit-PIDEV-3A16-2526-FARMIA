<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260429221659 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Create base ERP tables first
        $this->addSql('CREATE TABLE IF NOT EXISTS erp_service (id_service INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, prix DOUBLE PRECISION DEFAULT 0 NOT NULL, stock INT DEFAULT 0 NOT NULL, seuil_critique INT DEFAULT 0 NOT NULL, INDEX idx_erp_service_stock (stock), PRIMARY KEY(id_service)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS erp_achat (id_achat INT AUTO_INCREMENT NOT NULL, date_achat DATE NOT NULL, total DOUBLE PRECISION DEFAULT 0 NOT NULL, INDEX idx_erp_achat_date (date_achat), PRIMARY KEY(id_achat)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE IF NOT EXISTS erp_vente (id_vente INT AUTO_INCREMENT NOT NULL, date_vente DATE NOT NULL, total DOUBLE PRECISION DEFAULT 0 NOT NULL, PRIMARY KEY(id_vente)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        
        // Now create the dependent tables
        $this->addSql('CREATE TABLE IF NOT EXISTS erp_ligne_achat (id INT AUTO_INCREMENT NOT NULL, quantite DOUBLE PRECISION NOT NULL, prix_unitaire DOUBLE PRECISION NOT NULL, id_achat INT NOT NULL, id_matiere INT NOT NULL, INDEX IDX_17F2E914C82CC566 (id_achat), INDEX IDX_17F2E9144E89FE3A (id_matiere), UNIQUE INDEX uk_ligne_achat (id_achat, id_matiere), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS erp_ligne_vente (id INT AUTO_INCREMENT NOT NULL, quantite INT NOT NULL, prix_unitaire DOUBLE PRECISION NOT NULL, id_vente INT NOT NULL, id_produit INT NOT NULL, INDEX IDX_B9D1470E660F6B7C (id_vente), INDEX IDX_B9D1470EF7384557 (id_produit), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS erp_matiere (id_matiere INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, unite VARCHAR(50) DEFAULT \'unité\' NOT NULL, stock DOUBLE PRECISION DEFAULT 0 NOT NULL, prix_unitaire DOUBLE PRECISION DEFAULT 0 NOT NULL, seuil_critique DOUBLE PRECISION DEFAULT 0 NOT NULL, PRIMARY KEY(id_matiere)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS erp_produit (id_produit INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, prix_vente DOUBLE PRECISION DEFAULT 0 NOT NULL, stock INT DEFAULT 0 NOT NULL, PRIMARY KEY(id_produit)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS erp_recette_ingredient (id INT AUTO_INCREMENT NOT NULL, quantite DOUBLE PRECISION NOT NULL, id_produit INT NOT NULL, id_matiere INT NOT NULL, INDEX IDX_CFB418ACF7384557 (id_produit), INDEX IDX_CFB418AC4E89FE3A (id_matiere), UNIQUE INDEX uk_recette (id_produit, id_matiere), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        
        // Add foreign keys if they don't exist
        $this->addSql('ALTER TABLE erp_ligne_achat ADD CONSTRAINT FK_17F2E914C82CC566 FOREIGN KEY (id_achat) REFERENCES erp_achat (id_achat)');
        $this->addSql('ALTER TABLE erp_ligne_achat ADD CONSTRAINT FK_17F2E9144E89FE3A FOREIGN KEY (id_matiere) REFERENCES erp_matiere (id_matiere) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE erp_ligne_vente ADD CONSTRAINT FK_B9D1470E660F6B7C FOREIGN KEY (id_vente) REFERENCES erp_vente (id_vente)');
        $this->addSql('ALTER TABLE erp_ligne_vente ADD CONSTRAINT FK_B9D1470EF7384557 FOREIGN KEY (id_produit) REFERENCES erp_produit (id_produit) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE erp_recette_ingredient ADD CONSTRAINT FK_CFB418ACF7384557 FOREIGN KEY (id_produit) REFERENCES erp_produit (id_produit) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE erp_recette_ingredient ADD CONSTRAINT FK_CFB418AC4E89FE3A FOREIGN KEY (id_matiere) REFERENCES erp_matiere (id_matiere) ON DELETE RESTRICT');
        
        // Modify existing tables
        $this->addSql('ALTER TABLE erp_achat CHANGE total total DOUBLE PRECISION DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE erp_service CHANGE prix prix DOUBLE PRECISION DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE erp_vente CHANGE total total DOUBLE PRECISION DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE erp_ligne_achat DROP FOREIGN KEY FK_17F2E914C82CC566');
        $this->addSql('ALTER TABLE erp_ligne_achat DROP FOREIGN KEY FK_17F2E9144E89FE3A');
        $this->addSql('ALTER TABLE erp_ligne_vente DROP FOREIGN KEY FK_B9D1470E660F6B7C');
        $this->addSql('ALTER TABLE erp_ligne_vente DROP FOREIGN KEY FK_B9D1470EF7384557');
        $this->addSql('ALTER TABLE erp_recette_ingredient DROP FOREIGN KEY FK_CFB418ACF7384557');
        $this->addSql('ALTER TABLE erp_recette_ingredient DROP FOREIGN KEY FK_CFB418AC4E89FE3A');
        $this->addSql('DROP TABLE erp_ligne_achat');
        $this->addSql('DROP TABLE erp_ligne_vente');
        $this->addSql('DROP TABLE erp_matiere');
        $this->addSql('DROP TABLE erp_produit');
        $this->addSql('DROP TABLE erp_recette_ingredient');
        $this->addSql('ALTER TABLE erp_achat CHANGE total total DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE erp_service CHANGE prix prix DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE erp_vente CHANGE total total DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
    }
}
