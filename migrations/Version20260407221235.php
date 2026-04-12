<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260407221235 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE animal ADD CONSTRAINT FK_6AAB231F88D30FF2 FOREIGN KEY (id_ferme) REFERENCES ferme (id_ferme)');
        $this->addSql('CREATE INDEX IDX_6AAB231F88D30FF2 ON animal (id_ferme)');
        $this->addSql('ALTER TABLE plante ADD CONSTRAINT FK_517A694788D30FF2 FOREIGN KEY (id_ferme) REFERENCES ferme (id_ferme)');
        $this->addSql('CREATE INDEX IDX_517A694788D30FF2 ON plante (id_ferme)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE animal DROP FOREIGN KEY FK_6AAB231F88D30FF2');
        $this->addSql('DROP INDEX IDX_6AAB231F88D30FF2 ON animal');
        $this->addSql('ALTER TABLE plante DROP FOREIGN KEY FK_517A694788D30FF2');
        $this->addSql('DROP INDEX IDX_517A694788D30FF2 ON plante');
    }
}
