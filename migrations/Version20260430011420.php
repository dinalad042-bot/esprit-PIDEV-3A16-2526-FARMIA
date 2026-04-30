<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260430011420 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add seuil_critique to erp_produit';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE erp_produit ADD seuil_critique DOUBLE PRECISION DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE erp_produit DROP seuil_critique');
    }
}
