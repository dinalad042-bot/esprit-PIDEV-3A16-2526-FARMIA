<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260417190308 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Table suivi_sante already created by Version20260417185156, no-op
    }

    public function down(Schema $schema): void
    {
        // Table suivi_sante dropped by Version20260417185156, no-op
    }
}
