<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260411193339 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Tables already exist, just add the new ones
        // latitude/longitude/id_user already added by previous migrations
        // SQLite: use inline FOREIGN KEY in CREATE TABLE
        $this->addSql('CREATE TABLE analyse (id_analyse INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, date_analyse DATETIME DEFAULT NULL, resultat_technique LONGTEXT DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, id_technicien INT NOT NULL, id_ferme INT NOT NULL, CONSTRAINT FK_351B0C7E33A2AC9B FOREIGN KEY (id_technicien) REFERENCES user (id_user) ON DELETE CASCADE, CONSTRAINT FK_351B0C7E88D30FF2 FOREIGN KEY (id_ferme) REFERENCES ferme (id_ferme) ON DELETE CASCADE)');
        $this->addSql('CREATE INDEX IDX_351B0C7E33A2AC9B ON analyse (id_technicien)');
        $this->addSql('CREATE INDEX IDX_351B0C7E88D30FF2 ON analyse (id_ferme)');
        $this->addSql('CREATE TABLE conseil (id_conseil INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, description_conseil LONGTEXT NOT NULL, priorite VARCHAR(10) DEFAULT \'MOYENNE\' NOT NULL, id_analyse INT NOT NULL, CONSTRAINT FK_3F3F0681EB86A50E FOREIGN KEY (id_analyse) REFERENCES analyse (id_analyse) ON DELETE CASCADE)');
        $this->addSql('CREATE INDEX IDX_3F3F0681EB86A50E ON conseil (id_analyse)');
        // Foreign keys for existing tables - SQLite: use table recreation
        // animal and plante already have id_ferme, FK will be added via table recreation if needed
        // ferme already has id_user from Version20260406230210
    }

    public function down(Schema $schema): void
    {
        // No-op: SQLite does not support DROP CONSTRAINT. FKs are inline in CREATE TABLE.
        // animal/plante/ferme FKs are inline in their CREATE TABLE statements
        $this->addSql('DROP TABLE analyse');
        $this->addSql('DROP TABLE conseil');
        // latitude/longitude/id_user removed by previous migrations
    }
}
