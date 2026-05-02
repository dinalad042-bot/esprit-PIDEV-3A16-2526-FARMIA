<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260429224451 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE erp_achat CHANGE total total DOUBLE PRECISION DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE erp_matiere CHANGE stock stock DOUBLE PRECISION DEFAULT 0 NOT NULL, CHANGE prix_unitaire prix_unitaire DOUBLE PRECISION DEFAULT 0 NOT NULL, CHANGE seuil_critique seuil_critique DOUBLE PRECISION DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE erp_produit ADD quantite_produite DOUBLE PRECISION DEFAULT 1 NOT NULL, ADD is_simple TINYINT(1) DEFAULT 0 NOT NULL, CHANGE prix_vente prix_vente DOUBLE PRECISION DEFAULT 0 NOT NULL, CHANGE stock stock DOUBLE PRECISION DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE erp_service CHANGE prix prix DOUBLE PRECISION DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE erp_vente CHANGE total total DOUBLE PRECISION DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE erp_achat CHANGE total total DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE erp_matiere CHANGE stock stock DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE prix_unitaire prix_unitaire DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE seuil_critique seuil_critique DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE erp_produit DROP quantite_produite, DROP is_simple, CHANGE prix_vente prix_vente DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE stock stock INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE erp_service CHANGE prix prix DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE erp_vente CHANGE total total DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
    }
}
