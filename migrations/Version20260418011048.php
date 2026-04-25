<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260418011048 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // SQLite: recreate table to add column and modify column nullability
        $this->addSql('CREATE TABLE suivi_sante_new (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, date_consultation DATETIME NOT NULL, diagnostic LONGTEXT NOT NULL, etat_au_moment VARCHAR(50) DEFAULT NULL, id_animal INT NOT NULL, type VARCHAR(30) DEFAULT NULL, CONSTRAINT FK_6C0412484C9C96F2 FOREIGN KEY (id_animal) REFERENCES animal (id_animal) ON DELETE CASCADE)');
        $this->addSql('INSERT INTO suivi_sante_new (id, date_consultation, diagnostic, etat_au_moment, id_animal) SELECT id, date_consultation, diagnostic, etat_au_moment, id_animal FROM suivi_sante');
        $this->addSql('DROP TABLE suivi_sante');
        $this->addSql('ALTER TABLE suivi_sante_new RENAME TO suivi_sante');
        $this->addSql('CREATE INDEX IDX_6C0412484C9C96F2 ON suivi_sante (id_animal)');
    }

    public function down(Schema $schema): void
    {
        // SQLite: recreate table to remove column and restore NOT NULL
        $this->addSql('CREATE TABLE suivi_sante_new (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, date_consultation DATETIME NOT NULL, diagnostic LONGTEXT NOT NULL, etat_au_moment VARCHAR(50) NOT NULL, id_animal INT NOT NULL, CONSTRAINT FK_6C0412484C9C96F2 FOREIGN KEY (id_animal) REFERENCES animal (id_animal) ON DELETE CASCADE)');
        $this->addSql('INSERT INTO suivi_sante_new (id, date_consultation, diagnostic, etat_au_moment, id_animal) SELECT id, date_consultation, diagnostic, etat_au_moment, id_animal FROM suivi_sante');
        $this->addSql('DROP TABLE suivi_sante');
        $this->addSql('ALTER TABLE suivi_sante_new RENAME TO suivi_sante');
        $this->addSql('CREATE INDEX IDX_6C0412484C9C96F2 ON suivi_sante (id_animal)');
    }
}
