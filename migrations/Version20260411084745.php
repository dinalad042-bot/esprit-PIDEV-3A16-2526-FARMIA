<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260411084745 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_face DROP FOREIGN KEY FK_USER_FACE_USER_ID');
        $this->addSql('DROP INDEX idx_user_face_user ON user_face');
        $this->addSql('CREATE INDEX IDX_7F9537AEA76ED395 ON user_face (user_id)');
        $this->addSql('ALTER TABLE user_face ADD CONSTRAINT FK_USER_FACE_USER_ID FOREIGN KEY (user_id) REFERENCES user (id_user) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_face DROP FOREIGN KEY FK_7F9537AEA76ED395');
        $this->addSql('DROP INDEX idx_7f9537aea76ed395 ON user_face');
        $this->addSql('CREATE INDEX IDX_USER_FACE_USER ON user_face (user_id)');
        $this->addSql('ALTER TABLE user_face ADD CONSTRAINT FK_7F9537AEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id_user) ON DELETE CASCADE');
    }
}
