<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260411193339 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE analyse (id_analyse INT AUTO_INCREMENT NOT NULL, date_analyse DATETIME DEFAULT NULL, resultat_technique LONGTEXT DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, id_technicien INT NOT NULL, id_ferme INT NOT NULL, INDEX IDX_351B0C7E33A2AC9B (id_technicien), INDEX IDX_351B0C7E88D30FF2 (id_ferme), PRIMARY KEY(id_analyse)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE conseil (id_conseil INT AUTO_INCREMENT NOT NULL, description_conseil LONGTEXT NOT NULL, priorite VARCHAR(10) DEFAULT \'MOYENNE\', id_analyse INT NOT NULL, INDEX IDX_3F3F0681EB86A50E (id_analyse), PRIMARY KEY(id_conseil)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ferme (id_ferme INT AUTO_INCREMENT NOT NULL, nom_ferme VARCHAR(255) NOT NULL, lieu VARCHAR(255) NOT NULL, surface DOUBLE PRECISION DEFAULT 0, latitude DOUBLE PRECISION DEFAULT NULL, longitude DOUBLE PRECISION DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, id_user INT DEFAULT NULL, INDEX IDX_66564EC26B3CA4B (id_user), PRIMARY KEY(id_ferme)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id_user INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) DEFAULT NULL, prenom VARCHAR(100) DEFAULT NULL, email VARCHAR(150) NOT NULL, password VARCHAR(255) NOT NULL, cin VARCHAR(20) DEFAULT NULL, adresse LONGTEXT DEFAULT NULL, telephone VARCHAR(20) DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, role VARCHAR(50) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D649ABE530DA (cin), PRIMARY KEY(id_user)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_face (id INT AUTO_INCREMENT NOT NULL, image_path VARCHAR(500) DEFAULT NULL, samples_count INT DEFAULT 0 NOT NULL, is_active TINYINT(1) DEFAULT 1 NOT NULL, confidence_score DOUBLE PRECISION DEFAULT NULL, enrolled_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_7F9537AEA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_log (id BIGINT AUTO_INCREMENT NOT NULL, action_type VARCHAR(20) DEFAULT NULL, timestamp DATETIME DEFAULT NULL, description LONGTEXT DEFAULT NULL, user_id INT DEFAULT NULL, performed_by INT DEFAULT NULL, INDEX IDX_6429094EA76ED395 (user_id), INDEX IDX_6429094E99EB8EA2 (performed_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE analyse ADD CONSTRAINT FK_351B0C7E33A2AC9B FOREIGN KEY (id_technicien) REFERENCES user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE analyse ADD CONSTRAINT FK_351B0C7E88D30FF2 FOREIGN KEY (id_ferme) REFERENCES ferme (id_ferme) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conseil ADD CONSTRAINT FK_3F3F0681EB86A50E FOREIGN KEY (id_analyse) REFERENCES analyse (id_analyse) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ferme ADD CONSTRAINT FK_66564EC26B3CA4B FOREIGN KEY (id_user) REFERENCES user (id_user) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user_face ADD CONSTRAINT FK_7F9537AEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_log ADD CONSTRAINT FK_6429094EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id_user) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user_log ADD CONSTRAINT FK_6429094E99EB8EA2 FOREIGN KEY (performed_by) REFERENCES user (id_user) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE animal ADD CONSTRAINT FK_6AAB231F88D30FF2 FOREIGN KEY (id_ferme) REFERENCES ferme (id_ferme)');
        $this->addSql('ALTER TABLE plante ADD CONSTRAINT FK_517A694788D30FF2 FOREIGN KEY (id_ferme) REFERENCES ferme (id_ferme)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE analyse DROP FOREIGN KEY FK_351B0C7E33A2AC9B');
        $this->addSql('ALTER TABLE analyse DROP FOREIGN KEY FK_351B0C7E88D30FF2');
        $this->addSql('ALTER TABLE conseil DROP FOREIGN KEY FK_3F3F0681EB86A50E');
        $this->addSql('ALTER TABLE ferme DROP FOREIGN KEY FK_66564EC26B3CA4B');
        $this->addSql('ALTER TABLE user_face DROP FOREIGN KEY FK_7F9537AEA76ED395');
        $this->addSql('ALTER TABLE user_log DROP FOREIGN KEY FK_6429094EA76ED395');
        $this->addSql('ALTER TABLE user_log DROP FOREIGN KEY FK_6429094E99EB8EA2');
        $this->addSql('DROP TABLE analyse');
        $this->addSql('DROP TABLE conseil');
        $this->addSql('DROP TABLE ferme');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_face');
        $this->addSql('DROP TABLE user_log');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE animal DROP FOREIGN KEY FK_6AAB231F88D30FF2');
        $this->addSql('ALTER TABLE plante DROP FOREIGN KEY FK_517A694788D30FF2');
    }
}
