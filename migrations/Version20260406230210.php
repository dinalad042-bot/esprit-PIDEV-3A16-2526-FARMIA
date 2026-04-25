<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260406230210 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // latitude/longitude already added by Version20260405222132
        $this->addSql('ALTER TABLE ferme ADD id_user INT NOT NULL');
        $this->addSql('CREATE INDEX IDX_66564EC26B3CA4B ON ferme (id_user)');
        $this->addSql('ALTER TABLE plante RENAME COLUMN quantite TO quantite_old');
        $this->addSql('ALTER TABLE plante ADD quantite INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // latitude/longitude removed by Version20260405222132 down
        $this->addSql('ALTER TABLE ferme DROP id_user');
        $this->addSql('DROP INDEX IDX_66564EC26B3CA4B ON ferme');
        $this->addSql('ALTER TABLE plante DROP quantite');
        $this->addSql('ALTER TABLE plante RENAME COLUMN quantite_old TO quantite');
    }
}
