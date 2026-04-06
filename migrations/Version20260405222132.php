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
        $this->addSql('ALTER TABLE animal ADD etat_sante VARCHAR(255) NOT NULL, ADD date_naissance DATE NOT NULL, ADD id_ferme INT NOT NULL, CHANGE id id_animal INT AUTO_INCREMENT NOT NULL, CHANGE nom espece VARCHAR(255) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id_animal)');
        $this->addSql('ALTER TABLE ferme ADD lieu VARCHAR(255) NOT NULL, ADD surface DOUBLE PRECISION NOT NULL, CHANGE id id_ferme INT AUTO_INCREMENT NOT NULL, CHANGE nom nom_ferme VARCHAR(255) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id_ferme)');
        $this->addSql('ALTER TABLE plante ADD cycle_vie VARCHAR(255) NOT NULL, ADD id_ferme INT NOT NULL, ADD quantite DOUBLE PRECISION NOT NULL, CHANGE id id_plante INT AUTO_INCREMENT NOT NULL, CHANGE nom nom_espece VARCHAR(255) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id_plante)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE animal ADD nom VARCHAR(255) NOT NULL, DROP espece, DROP etat_sante, DROP date_naissance, DROP id_ferme, CHANGE id_animal id INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE ferme ADD nom VARCHAR(255) NOT NULL, DROP nom_ferme, DROP lieu, DROP surface, CHANGE id_ferme id INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE plante ADD nom VARCHAR(255) NOT NULL, DROP nom_espece, DROP cycle_vie, DROP id_ferme, DROP quantite, CHANGE id_plante id INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
    }
}
