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
        $this->addSql('CREATE TABLE suivi_sante (id INT AUTO_INCREMENT NOT NULL, date_consultation DATETIME NOT NULL, diagnostic LONGTEXT NOT NULL, etat_au_moment VARCHAR(50) NOT NULL, id_animal INT NOT NULL, INDEX IDX_6C0412484C9C96F2 (id_animal), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE suivi_sante ADD CONSTRAINT FK_6C0412484C9C96F2 FOREIGN KEY (id_animal) REFERENCES animal (id_animal) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_log ADD CONSTRAINT FK_6429094EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id_user)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE suivi_sante DROP FOREIGN KEY FK_6C0412484C9C96F2');
        $this->addSql('DROP TABLE suivi_sante');
        $this->addSql('ALTER TABLE user_log DROP FOREIGN KEY FK_6429094EA76ED395');
    }
}
