<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260417185156 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // SQLite: use inline FOREIGN KEY in CREATE TABLE
        $this->addSql('CREATE TABLE suivi_sante (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, date_consultation DATETIME NOT NULL, diagnostic LONGTEXT NOT NULL, etat_au_moment VARCHAR(50) NOT NULL, id_animal INT NOT NULL, CONSTRAINT FK_6C0412484C9C96F2 FOREIGN KEY (id_animal) REFERENCES animal (id_animal) ON DELETE CASCADE)');
        $this->addSql('CREATE INDEX IDX_6C0412484C9C96F2 ON suivi_sante (id_animal)');
        // FK for user_log already inline in Version20260408110851, skip ADD CONSTRAINT
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // SQLite: FK is inline in CREATE TABLE, just drop the table
        $this->addSql('DROP TABLE suivi_sante');
    }
}
