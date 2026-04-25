<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260407221754 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE plante_besoin (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom_espece VARCHAR(50) NOT NULL, frequence_arrosage INT NOT NULL, type_engrais VARCHAR(100) NOT NULL, frequence_engrais INT NOT NULL)');
        // SQLite doesn't support ADD CONSTRAINT, foreign keys are created inline
        // This migration is a no-op for SQLite
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE plante_besoin');
    }
}
