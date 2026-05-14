<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260506103025 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE suivi_sante DROP FOREIGN KEY FK_6C0412484C9C96F2');
        $this->addSql('DROP INDEX IDX_6C0412484C9C96F2 ON suivi_sante');
        $this->addSql('ALTER TABLE suivi_sante CHANGE id_animal animal_id INT NOT NULL');
        $this->addSql('ALTER TABLE suivi_sante ADD CONSTRAINT FK_6C0412488E962C16 FOREIGN KEY (animal_id) REFERENCES animal (id_animal) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_6C0412488E962C16 ON suivi_sante (animal_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE suivi_sante DROP FOREIGN KEY FK_6C0412488E962C16');
        $this->addSql('DROP INDEX IDX_6C0412488E962C16 ON suivi_sante');
        $this->addSql('ALTER TABLE suivi_sante CHANGE animal_id id_animal INT NOT NULL');
        $this->addSql('ALTER TABLE suivi_sante ADD CONSTRAINT FK_6C0412484C9C96F2 FOREIGN KEY (id_animal) REFERENCES animal (id_animal) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_6C0412484C9C96F2 ON suivi_sante (id_animal)');
    }
}
