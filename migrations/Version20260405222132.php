<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260405222132 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE animal ADD etat_sante VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE animal ADD date_naissance DATE NOT NULL');
        $this->addSql('ALTER TABLE animal ADD id_ferme INT NOT NULL');
        $this->addSql('ALTER TABLE animal ADD espece VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE animal SET espece = nom');
        $this->addSql('ALTER TABLE animal DROP nom');
        $this->addSql('ALTER TABLE ferme ADD lieu VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE ferme ADD surface DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE ferme ADD nom_ferme VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE ferme SET nom_ferme = nom');
        $this->addSql('ALTER TABLE ferme DROP nom');
        $this->addSql('ALTER TABLE plante ADD cycle_vie VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE plante ADD id_ferme INT NOT NULL');
        $this->addSql('ALTER TABLE plante ADD quantite DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE plante ADD nom_espece VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE plante SET nom_espece = nom');
        $this->addSql('ALTER TABLE plante DROP nom');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE animal ADD nom VARCHAR(255) NOT NULL');
        $this->addSql('UPDATE animal SET nom = espece');
        $this->addSql('ALTER TABLE animal DROP espece');
        $this->addSql('ALTER TABLE animal DROP etat_sante');
        $this->addSql('ALTER TABLE animal DROP date_naissance');
        $this->addSql('ALTER TABLE animal DROP id_ferme');
        $this->addSql('ALTER TABLE ferme ADD nom VARCHAR(255) NOT NULL');
        $this->addSql('UPDATE ferme SET nom = nom_ferme');
        $this->addSql('ALTER TABLE ferme DROP nom_ferme');
        $this->addSql('ALTER TABLE ferme DROP lieu');
        $this->addSql('ALTER TABLE ferme DROP surface');
        $this->addSql('ALTER TABLE plante ADD nom VARCHAR(255) NOT NULL');
        $this->addSql('UPDATE plante SET nom = nom_espece');
        $this->addSql('ALTER TABLE plante DROP nom_espece');
        $this->addSql('ALTER TABLE plante DROP cycle_vie');
        $this->addSql('ALTER TABLE plante DROP id_ferme');
        $this->addSql('ALTER TABLE plante DROP quantite');
    }
}
