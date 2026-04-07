<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260407183249 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE animaux DROP FOREIGN KEY animaux_ibfk_1');
        $this->addSql('ALTER TABLE face_data DROP FOREIGN KEY face_data_ibfk_1');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY notification_ibfk_1');
        $this->addSql('ALTER TABLE plantes DROP FOREIGN KEY plantes_ibfk_1');
        $this->addSql('DROP TABLE animaux');
        $this->addSql('DROP TABLE face_data');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE plantes');
        $this->addSql('ALTER TABLE analyse DROP FOREIGN KEY analyse_ibfk_2');
        $this->addSql('ALTER TABLE analyse DROP FOREIGN KEY analyse_ibfk_1');
        $this->addSql('ALTER TABLE analyse CHANGE date_analyse date_analyse DATETIME DEFAULT NULL, CHANGE resultat_technique resultat_technique LONGTEXT DEFAULT NULL');
        $this->addSql('DROP INDEX id_technicien ON analyse');
        $this->addSql('CREATE INDEX IDX_351B0C7E33A2AC9B ON analyse (id_technicien)');
        $this->addSql('DROP INDEX id_ferme ON analyse');
        $this->addSql('CREATE INDEX IDX_351B0C7E88D30FF2 ON analyse (id_ferme)');
        $this->addSql('ALTER TABLE analyse ADD CONSTRAINT analyse_ibfk_2 FOREIGN KEY (id_ferme) REFERENCES ferme (id_ferme) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE analyse ADD CONSTRAINT analyse_ibfk_1 FOREIGN KEY (id_technicien) REFERENCES user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conseil DROP FOREIGN KEY conseil_ibfk_1');
        $this->addSql('ALTER TABLE conseil CHANGE description_conseil description_conseil LONGTEXT NOT NULL, CHANGE priorite priorite VARCHAR(10) DEFAULT \'MOYENNE\'');
        $this->addSql('DROP INDEX id_analyse ON conseil');
        $this->addSql('CREATE INDEX IDX_3F3F0681EB86A50E ON conseil (id_analyse)');
        $this->addSql('ALTER TABLE conseil ADD CONSTRAINT conseil_ibfk_1 FOREIGN KEY (id_analyse) REFERENCES analyse (id_analyse) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ferme DROP FOREIGN KEY ferme_ibfk_1');
        $this->addSql('DROP INDEX id_fermier ON ferme');
        $this->addSql('ALTER TABLE ferme CHANGE surface surface DOUBLE PRECISION DEFAULT 0, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE nom nom VARCHAR(100) DEFAULT NULL, CHANGE prenom prenom VARCHAR(100) DEFAULT NULL, CHANGE cin cin VARCHAR(20) DEFAULT NULL, CHANGE adresse adresse LONGTEXT DEFAULT NULL, CHANGE role role VARCHAR(50) DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('DROP INDEX unique_email ON user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
        $this->addSql('DROP INDEX unique_cin ON user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649ABE530DA ON user (cin)');
        $this->addSql('ALTER TABLE user_log DROP FOREIGN KEY user_log_ibfk_1');
        $this->addSql('ALTER TABLE user_log DROP FOREIGN KEY user_log_ibfk_1');
        $this->addSql('ALTER TABLE user_log ADD id BIGINT AUTO_INCREMENT NOT NULL, ADD action_type VARCHAR(20) DEFAULT NULL, DROP id_log, DROP action, CHANGE user_id user_id INT DEFAULT NULL, CHANGE performed_by performed_by INT DEFAULT NULL, CHANGE description description LONGTEXT DEFAULT NULL, CHANGE timestamp timestamp DATETIME DEFAULT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE user_log ADD CONSTRAINT FK_6429094EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id_user)');
        $this->addSql('ALTER TABLE user_log ADD CONSTRAINT FK_6429094E99EB8EA2 FOREIGN KEY (performed_by) REFERENCES user (id_user)');
        $this->addSql('CREATE INDEX IDX_6429094E99EB8EA2 ON user_log (performed_by)');
        $this->addSql('DROP INDEX user_id ON user_log');
        $this->addSql('CREATE INDEX IDX_6429094EA76ED395 ON user_log (user_id)');
        $this->addSql('ALTER TABLE user_log ADD CONSTRAINT user_log_ibfk_1 FOREIGN KEY (user_id) REFERENCES user (id_user) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE animaux (id_animal INT AUTO_INCREMENT NOT NULL, espece VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, etat_sante VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'Bon\' COLLATE `utf8mb4_unicode_ci`, date_naissance DATE DEFAULT NULL, id_ferme INT NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX id_ferme (id_ferme), PRIMARY KEY(id_animal)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE face_data (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, face_model LONGBLOB NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, UNIQUE INDEX unique_user (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE notification (id_notification INT AUTO_INCREMENT NOT NULL, id_user INT NOT NULL, titre VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, message TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, type VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, id_reference INT DEFAULT NULL, is_read TINYINT(1) DEFAULT 0, date_creation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX idx_user_read (id_user, is_read), INDEX IDX_BF5476CA6B3CA4B (id_user), PRIMARY KEY(id_notification)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE plantes (id_plante INT AUTO_INCREMENT NOT NULL, nom_espece VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, cycle_vie VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, id_ferme INT NOT NULL, quantite DOUBLE PRECISION DEFAULT \'0\', created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX id_ferme (id_ferme), PRIMARY KEY(id_plante)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE animaux ADD CONSTRAINT animaux_ibfk_1 FOREIGN KEY (id_ferme) REFERENCES ferme (id_ferme) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE face_data ADD CONSTRAINT face_data_ibfk_1 FOREIGN KEY (user_id) REFERENCES user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT notification_ibfk_1 FOREIGN KEY (id_user) REFERENCES user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE plantes ADD CONSTRAINT plantes_ibfk_1 FOREIGN KEY (id_ferme) REFERENCES ferme (id_ferme) ON DELETE CASCADE');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE analyse DROP FOREIGN KEY FK_351B0C7E33A2AC9B');
        $this->addSql('ALTER TABLE analyse DROP FOREIGN KEY FK_351B0C7E88D30FF2');
        $this->addSql('ALTER TABLE analyse CHANGE date_analyse date_analyse DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE resultat_technique resultat_technique TEXT DEFAULT NULL');
        $this->addSql('DROP INDEX idx_351b0c7e33a2ac9b ON analyse');
        $this->addSql('CREATE INDEX id_technicien ON analyse (id_technicien)');
        $this->addSql('DROP INDEX idx_351b0c7e88d30ff2 ON analyse');
        $this->addSql('CREATE INDEX id_ferme ON analyse (id_ferme)');
        $this->addSql('ALTER TABLE analyse ADD CONSTRAINT FK_351B0C7E33A2AC9B FOREIGN KEY (id_technicien) REFERENCES user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE analyse ADD CONSTRAINT FK_351B0C7E88D30FF2 FOREIGN KEY (id_ferme) REFERENCES ferme (id_ferme) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conseil DROP FOREIGN KEY FK_3F3F0681EB86A50E');
        $this->addSql('ALTER TABLE conseil CHANGE description_conseil description_conseil TEXT NOT NULL, CHANGE priorite priorite ENUM(\'HAUTE\', \'MOYENNE\', \'BASSE\') DEFAULT \'MOYENNE\'');
        $this->addSql('DROP INDEX idx_3f3f0681eb86a50e ON conseil');
        $this->addSql('CREATE INDEX id_analyse ON conseil (id_analyse)');
        $this->addSql('ALTER TABLE conseil ADD CONSTRAINT FK_3F3F0681EB86A50E FOREIGN KEY (id_analyse) REFERENCES analyse (id_analyse) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ferme CHANGE surface surface DOUBLE PRECISION DEFAULT \'0\', CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE ferme ADD CONSTRAINT ferme_ibfk_1 FOREIGN KEY (id_fermier) REFERENCES user (id_user) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX id_fermier ON ferme (id_fermier)');
        $this->addSql('ALTER TABLE user CHANGE nom nom VARCHAR(100) NOT NULL, CHANGE prenom prenom VARCHAR(100) NOT NULL, CHANGE cin cin VARCHAR(20) NOT NULL, CHANGE adresse adresse TEXT DEFAULT NULL, CHANGE role role ENUM(\'ADMIN\', \'EXPERT\', \'AGRICOLE\', \'FOURNISSEUR\') DEFAULT \'AGRICOLE\', CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('DROP INDEX uniq_8d93d649e7927c74 ON user');
        $this->addSql('CREATE UNIQUE INDEX unique_email ON user (email)');
        $this->addSql('DROP INDEX uniq_8d93d649abe530da ON user');
        $this->addSql('CREATE UNIQUE INDEX unique_cin ON user (cin)');
        $this->addSql('ALTER TABLE user_log DROP FOREIGN KEY FK_6429094EA76ED395');
        $this->addSql('ALTER TABLE user_log DROP FOREIGN KEY FK_6429094E99EB8EA2');
        $this->addSql('DROP INDEX IDX_6429094E99EB8EA2 ON user_log');
        $this->addSql('ALTER TABLE user_log DROP FOREIGN KEY FK_6429094EA76ED395');
        $this->addSql('ALTER TABLE user_log ADD id_log INT AUTO_INCREMENT NOT NULL, ADD action ENUM(\'CREATE\', \'UPDATE\', \'DELETE\', \'LOGIN\', \'LOGOUT\') NOT NULL, DROP id, DROP action_type, CHANGE timestamp timestamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE description description TEXT DEFAULT NULL, CHANGE user_id user_id INT NOT NULL, CHANGE performed_by performed_by VARCHAR(150) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id_log)');
        $this->addSql('ALTER TABLE user_log ADD CONSTRAINT user_log_ibfk_1 FOREIGN KEY (user_id) REFERENCES user (id_user) ON DELETE CASCADE');
        $this->addSql('DROP INDEX idx_6429094ea76ed395 ON user_log');
        $this->addSql('CREATE INDEX user_id ON user_log (user_id)');
        $this->addSql('ALTER TABLE user_log ADD CONSTRAINT FK_6429094EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id_user)');
    }
}
