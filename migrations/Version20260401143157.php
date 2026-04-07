<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260401143157 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_log DROP FOREIGN KEY user_log_ibfk_1');
        $this->addSql('ALTER TABLE user_log CHANGE performed_by performed_by INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_log ADD CONSTRAINT FK_6429094E99EB8EA2 FOREIGN KEY (performed_by) REFERENCES user (id_user)');
        $this->addSql('CREATE INDEX IDX_6429094E99EB8EA2 ON user_log (performed_by)');
        $this->addSql('DROP INDEX IF EXISTS fk_6429094ea76ed395 ON user_log');
        $this->addSql('CREATE INDEX IDX_6429094EA76ED395 ON user_log (user_id)');
        $this->addSql('ALTER TABLE user_log ADD CONSTRAINT FK_6429094EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id_user)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_log DROP FOREIGN KEY FK_6429094E99EB8EA2');
        $this->addSql('DROP INDEX IDX_6429094E99EB8EA2 ON user_log');
        $this->addSql('ALTER TABLE user_log DROP FOREIGN KEY FK_6429094EA76ED395');
        $this->addSql('ALTER TABLE user_log CHANGE performed_by performed_by VARCHAR(255) DEFAULT NULL');
        $this->addSql('DROP INDEX idx_6429094ea76ed395 ON user_log');
        $this->addSql('CREATE INDEX FK_6429094EA76ED395 ON user_log (user_id)');
        $this->addSql('ALTER TABLE user_log ADD CONSTRAINT FK_6429094EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id_user)');
    }
}
