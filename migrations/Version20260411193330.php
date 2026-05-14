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
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user (id_user INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) DEFAULT NULL, prenom VARCHAR(100) DEFAULT NULL, email VARCHAR(150) NOT NULL, password VARCHAR(255) NOT NULL, cin VARCHAR(8) DEFAULT NULL, adresse LONGTEXT DEFAULT NULL, telephone VARCHAR(8) DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, role VARCHAR(50) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D649ABE530DA (cin), PRIMARY KEY(id_user)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_face (id INT AUTO_INCREMENT NOT NULL, image_path VARCHAR(500) DEFAULT NULL, samples_count INT DEFAULT 0 NOT NULL, is_active TINYINT(1) DEFAULT 1 NOT NULL, confidence_score DOUBLE PRECISION DEFAULT NULL, enrolled_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_7F9537AEA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_log (id BIGINT AUTO_INCREMENT NOT NULL, action_type VARCHAR(20) DEFAULT NULL, timestamp DATETIME DEFAULT NULL, description LONGTEXT DEFAULT NULL, user_id INT DEFAULT NULL, performed_by INT DEFAULT NULL, INDEX IDX_6429094EA76ED395 (user_id), INDEX IDX_6429094E99EB8EA2 (performed_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE user_face ADD CONSTRAINT FK_7F9537AEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_log ADD CONSTRAINT FK_6429094EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id_user) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user_log ADD CONSTRAINT FK_6429094E99EB8EA2 FOREIGN KEY (performed_by) REFERENCES user (id_user) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_face DROP FOREIGN KEY FK_7F9537AEA76ED395');
        $this->addSql('ALTER TABLE user_log DROP FOREIGN KEY FK_6429094EA76ED395');
        $this->addSql('ALTER TABLE user_log DROP FOREIGN KEY FK_6429094E99EB8EA2');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_face');
        $this->addSql('DROP TABLE user_log');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
