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
        $this->addSql('ALTER TABLE ferme ADD latitude DOUBLE PRECISION DEFAULT NULL, ADD longitude DOUBLE PRECISION DEFAULT NULL, ADD id_user INT NOT NULL');
        $this->addSql('ALTER TABLE ferme ADD CONSTRAINT FK_66564EC26B3CA4B FOREIGN KEY (id_user) REFERENCES user (id_user)');
        $this->addSql('CREATE INDEX IDX_66564EC26B3CA4B ON ferme (id_user)');
        $this->addSql('ALTER TABLE plante CHANGE quantite quantite INT NOT NULL');
        $this->addSql('ALTER TABLE user_log ADD CONSTRAINT FK_6429094EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id_user)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ferme DROP FOREIGN KEY FK_66564EC26B3CA4B');
        $this->addSql('DROP INDEX IDX_66564EC26B3CA4B ON ferme');
        $this->addSql('ALTER TABLE ferme DROP latitude, DROP longitude, DROP id_user');
        $this->addSql('ALTER TABLE plante CHANGE quantite quantite DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE user_log DROP FOREIGN KEY FK_6429094EA76ED395');
    }
}
