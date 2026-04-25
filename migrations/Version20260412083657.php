<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260412083657 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // SQLite: use table recreation for column modifications
        // FK was already inline in Version20260411193339, no need to drop/add
        $this->addSql('CREATE TABLE analyse_new (id_analyse INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, date_analyse DATETIME DEFAULT NULL, resultat_technique LONGTEXT DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, id_technicien INT NOT NULL, id_ferme INT NOT NULL, statut VARCHAR(20) NOT NULL DEFAULT \'en_attente\', description_demande LONGTEXT DEFAULT NULL, id_demandeur INT DEFAULT NULL, id_animal_cible INT DEFAULT NULL, id_plante_cible INT DEFAULT NULL, CONSTRAINT FK_351B0C7E33A2AC9B FOREIGN KEY (id_technicien) REFERENCES user (id_user) ON DELETE CASCADE, CONSTRAINT FK_351B0C7E88D30FF2 FOREIGN KEY (id_ferme) REFERENCES ferme (id_ferme) ON DELETE CASCADE)');
        $this->addSql('INSERT INTO analyse_new (id_analyse, date_analyse, resultat_technique, image_url, id_technicien, id_ferme) SELECT id_analyse, date_analyse, resultat_technique, image_url, id_technicien, id_ferme FROM analyse');
        $this->addSql('DROP TABLE analyse');
        $this->addSql('ALTER TABLE analyse_new RENAME TO analyse');
        $this->addSql('CREATE INDEX IDX_351B0C7E33A2AC9B ON analyse (id_technicien)');
        $this->addSql('CREATE INDEX IDX_351B0C7E88D30FF2 ON analyse (id_ferme)');
        $this->addSql('CREATE INDEX IDX_351B0C7EE6681A34 ON analyse (id_demandeur)');
        $this->addSql('CREATE INDEX IDX_351B0C7EE36F40BD ON analyse (id_animal_cible)');
        $this->addSql('CREATE INDEX IDX_351B0C7EA7275E42 ON analyse (id_plante_cible)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // SQLite: use table recreation for column modifications
        $this->addSql('DROP INDEX IDX_351B0C7EE6681A34');
        $this->addSql('DROP INDEX IDX_351B0C7EE36F40BD');
        $this->addSql('DROP INDEX IDX_351B0C7EA7275E42');
        $this->addSql('CREATE TABLE analyse_new (id_analyse INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, date_analyse DATETIME DEFAULT NULL, resultat_technique LONGTEXT DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, id_technicien INT NOT NULL, id_ferme INT NOT NULL, CONSTRAINT FK_351B0C7E33A2AC9B FOREIGN KEY (id_technicien) REFERENCES user (id_user) ON DELETE CASCADE, CONSTRAINT FK_351B0C7E88D30FF2 FOREIGN KEY (id_ferme) REFERENCES ferme (id_ferme) ON DELETE CASCADE)');
        $this->addSql('INSERT INTO analyse_new (id_analyse, date_analyse, resultat_technique, image_url, id_technicien, id_ferme) SELECT id_analyse, date_analyse, resultat_technique, image_url, id_technicien, id_ferme FROM analyse');
        $this->addSql('DROP TABLE analyse');
        $this->addSql('ALTER TABLE analyse_new RENAME TO analyse');
        $this->addSql('CREATE INDEX IDX_351B0C7E33A2AC9B ON analyse (id_technicien)');
        $this->addSql('CREATE INDEX IDX_351B0C7E88D30FF2 ON analyse (id_ferme)');
    }
}
