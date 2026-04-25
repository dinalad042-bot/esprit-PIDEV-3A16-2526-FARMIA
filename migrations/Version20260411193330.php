<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260411193330 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Tables already created by previous migrations, just add foreign keys
        // No-op: SQLite does not support ALTER TABLE ... ADD CONSTRAINT. FKs are inline in CREATE TABLE.
    }

    public function down(Schema $schema): void
    {
        // No-op: SQLite does not support DROP CONSTRAINT. FKs are inline in CREATE TABLE.
    }
}
