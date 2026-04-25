<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260423084644 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // SQLite: separate ADD COLUMN statements
        $this->addSql('ALTER TABLE analyse ADD COLUMN weather_data JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE analyse ADD COLUMN weather_fetched_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // SQLite: recreate table to drop columns
        $this->addSql('CREATE TABLE analyse_new (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_animal INT DEFAULT NULL, id_user INT DEFAULT NULL, date_analyse DATETIME NOT NULL, image_analyse VARCHAR(255) NOT NULL, diagnostic LONGTEXT NOT NULL, etat_analyse VARCHAR(50) NOT NULL, CONSTRAINT FK_12B455B34C9C96F2 FOREIGN KEY (id_animal) REFERENCES animal (id_animal), CONSTRAINT FK_12B455B3FE6E88D7 FOREIGN KEY (id_user) REFERENCES user (id_user))');
        $this->addSql('INSERT INTO analyse_new (id, id_animal, id_user, date_analyse, image_analyse, diagnostic, etat_analyse) SELECT id, id_animal, id_user, date_analyse, image_analyse, diagnostic, etat_analyse FROM analyse');
        $this->addSql('DROP TABLE analyse');
        $this->addSql('ALTER TABLE analyse_new RENAME TO analyse');
        $this->addSql('CREATE INDEX IDX_12B455B34C9C96F2 ON analyse (id_animal)');
        $this->addSql('CREATE INDEX IDX_12B455B3FE6E88D7 ON analyse (id_user)');
    }
}
