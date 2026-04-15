<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260408110343 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE animal (id_animal INT AUTO_INCREMENT NOT NULL, espece VARCHAR(255) NOT NULL, etat_sante VARCHAR(255) NOT NULL, date_naissance DATE NOT NULL, id_ferme INT NOT NULL, INDEX IDX_6AAB231F88D30FF2 (id_ferme), PRIMARY KEY(id_animal)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE plante (id_plante INT AUTO_INCREMENT NOT NULL, nom_espece VARCHAR(255) NOT NULL, cycle_vie VARCHAR(255) NOT NULL, quantite INT NOT NULL, id_ferme INT NOT NULL, INDEX IDX_517A694788D30FF2 (id_ferme), PRIMARY KEY(id_plante)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE animal ADD CONSTRAINT FK_6AAB231F88D30FF2 FOREIGN KEY (id_ferme) REFERENCES ferme (id_ferme)');
        $this->addSql('ALTER TABLE plante ADD CONSTRAINT FK_517A694788D30FF2 FOREIGN KEY (id_ferme) REFERENCES ferme (id_ferme)');
        $this->addSql('ALTER TABLE ferme ADD latitude DOUBLE PRECISION DEFAULT NULL, ADD longitude DOUBLE PRECISION DEFAULT NULL, ADD id_user INT DEFAULT NULL, DROP id_fermier, CHANGE nom_ferme nom_ferme VARCHAR(255) NOT NULL, CHANGE surface surface DOUBLE PRECISION DEFAULT 0');
        $this->addSql('ALTER TABLE ferme ADD CONSTRAINT FK_66564EC26B3CA4B FOREIGN KEY (id_user) REFERENCES user (id_user) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_66564EC26B3CA4B ON ferme (id_user)');
        $this->addSql('ALTER TABLE user_log ADD id BIGINT AUTO_INCREMENT NOT NULL, DROP id_log, DROP action, CHANGE user_id user_id INT DEFAULT NULL, CHANGE description description LONGTEXT DEFAULT NULL, CHANGE timestamp timestamp DATETIME DEFAULT NULL, CHANGE action_type action_type VARCHAR(20) DEFAULT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE user_log ADD CONSTRAINT FK_6429094EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id_user)');
        $this->addSql('ALTER TABLE user_log ADD CONSTRAINT FK_6429094E99EB8EA2 FOREIGN KEY (performed_by) REFERENCES user (id_user)');
        $this->addSql('CREATE INDEX IDX_6429094E99EB8EA2 ON user_log (performed_by)');
        $this->addSql('DROP INDEX user_id ON user_log');
        $this->addSql('CREATE INDEX IDX_6429094EA76ED395 ON user_log (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE animal DROP FOREIGN KEY FK_6AAB231F88D30FF2');
        $this->addSql('ALTER TABLE plante DROP FOREIGN KEY FK_517A694788D30FF2');
        $this->addSql('DROP TABLE animal');
        $this->addSql('DROP TABLE plante');
        $this->addSql('ALTER TABLE ferme DROP FOREIGN KEY FK_66564EC26B3CA4B');
        $this->addSql('DROP INDEX IDX_66564EC26B3CA4B ON ferme');
        $this->addSql('ALTER TABLE ferme ADD id_fermier INT NOT NULL, DROP latitude, DROP longitude, DROP id_user, CHANGE nom_ferme nom_ferme VARCHAR(100) NOT NULL, CHANGE surface surface DOUBLE PRECISION DEFAULT \'0\'');
        $this->addSql('ALTER TABLE user_log DROP FOREIGN KEY FK_6429094EA76ED395');
        $this->addSql('ALTER TABLE user_log DROP FOREIGN KEY FK_6429094E99EB8EA2');
        $this->addSql('DROP INDEX IDX_6429094E99EB8EA2 ON user_log');
        $this->addSql('ALTER TABLE user_log DROP FOREIGN KEY FK_6429094EA76ED395');
        $this->addSql('ALTER TABLE user_log ADD id_log INT AUTO_INCREMENT NOT NULL, ADD action ENUM(\'CREATE\', \'UPDATE\', \'DELETE\', \'LOGIN\', \'LOGOUT\') NOT NULL, DROP id, CHANGE action_type action_type VARCHAR(255) DEFAULT \'login\' NOT NULL, CHANGE timestamp timestamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE description description TEXT DEFAULT NULL, CHANGE user_id user_id INT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id_log)');
        $this->addSql('DROP INDEX idx_6429094ea76ed395 ON user_log');
        $this->addSql('CREATE INDEX user_id ON user_log (user_id)');
        $this->addSql('ALTER TABLE user_log ADD CONSTRAINT FK_6429094EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id_user)');
    }
}
