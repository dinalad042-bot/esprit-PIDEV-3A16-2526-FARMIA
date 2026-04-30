<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260407231810 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE animal (id_animal INT AUTO_INCREMENT NOT NULL, espece VARCHAR(255) NOT NULL, etat_sante VARCHAR(255) NOT NULL, date_naissance DATE NOT NULL, id_ferme INT NOT NULL, INDEX IDX_6AAB231F88D30FF2 (id_ferme), PRIMARY KEY(id_animal)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE erp_achat (id_achat INT AUTO_INCREMENT NOT NULL, date_achat DATE NOT NULL, total DOUBLE PRECISION DEFAULT 0 NOT NULL, INDEX idx_erp_achat_date (date_achat), PRIMARY KEY(id_achat)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE erp_liste_achat (id INT AUTO_INCREMENT NOT NULL, quantite INT NOT NULL, prix_unitaire DOUBLE PRECISION NOT NULL, id_achat INT NOT NULL, id_service INT NOT NULL, INDEX IDX_BA5D78AC82CC566 (id_achat), INDEX IDX_BA5D78A3F0033A2 (id_service), UNIQUE INDEX uk_erp_liste_achat (id_achat, id_service), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE erp_liste_vente (id INT AUTO_INCREMENT NOT NULL, quantite INT NOT NULL, prix_unitaire DOUBLE PRECISION NOT NULL, id_vente INT NOT NULL, id_service INT NOT NULL, INDEX IDX_A5867990660F6B7C (id_vente), INDEX IDX_A58679903F0033A2 (id_service), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE erp_service (id_service INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, prix DOUBLE PRECISION DEFAULT 0 NOT NULL, stock INT DEFAULT 0 NOT NULL, seuil_critique INT DEFAULT 0 NOT NULL, INDEX idx_erp_service_stock (stock), PRIMARY KEY(id_service)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE erp_vente (id_vente INT AUTO_INCREMENT NOT NULL, date_vente DATE NOT NULL, total DOUBLE PRECISION DEFAULT 0 NOT NULL, PRIMARY KEY(id_vente)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ferme (id_ferme INT AUTO_INCREMENT NOT NULL, nom_ferme VARCHAR(255) NOT NULL, lieu VARCHAR(255) NOT NULL, surface DOUBLE PRECISION NOT NULL, latitude DOUBLE PRECISION DEFAULT NULL, longitude DOUBLE PRECISION DEFAULT NULL, id_user INT DEFAULT NULL, INDEX IDX_66564EC26B3CA4B (id_user), PRIMARY KEY(id_ferme)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE plante (id_plante INT AUTO_INCREMENT NOT NULL, nom_espece VARCHAR(255) NOT NULL, cycle_vie VARCHAR(255) NOT NULL, quantite INT NOT NULL, id_ferme INT NOT NULL, INDEX IDX_517A694788D30FF2 (id_ferme), PRIMARY KEY(id_plante)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id_user INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) DEFAULT NULL, prenom VARCHAR(100) DEFAULT NULL, email VARCHAR(150) NOT NULL, password VARCHAR(255) NOT NULL, cin VARCHAR(20) DEFAULT NULL, adresse LONGTEXT DEFAULT NULL, telephone VARCHAR(20) DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, role VARCHAR(50) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D649ABE530DA (cin), PRIMARY KEY(id_user)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_log (id BIGINT AUTO_INCREMENT NOT NULL, action_type VARCHAR(20) DEFAULT NULL, timestamp DATETIME DEFAULT NULL, description LONGTEXT DEFAULT NULL, user_id INT DEFAULT NULL, performed_by INT DEFAULT NULL, INDEX IDX_6429094EA76ED395 (user_id), INDEX IDX_6429094E99EB8EA2 (performed_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE animal ADD CONSTRAINT FK_6AAB231F88D30FF2 FOREIGN KEY (id_ferme) REFERENCES ferme (id_ferme)');
        $this->addSql('ALTER TABLE erp_liste_achat ADD CONSTRAINT FK_BA5D78AC82CC566 FOREIGN KEY (id_achat) REFERENCES erp_achat (id_achat)');
        $this->addSql('ALTER TABLE erp_liste_achat ADD CONSTRAINT FK_BA5D78A3F0033A2 FOREIGN KEY (id_service) REFERENCES erp_service (id_service) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE erp_liste_vente ADD CONSTRAINT FK_A5867990660F6B7C FOREIGN KEY (id_vente) REFERENCES erp_vente (id_vente)');
        $this->addSql('ALTER TABLE erp_liste_vente ADD CONSTRAINT FK_A58679903F0033A2 FOREIGN KEY (id_service) REFERENCES erp_service (id_service) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE ferme ADD CONSTRAINT FK_66564EC26B3CA4B FOREIGN KEY (id_user) REFERENCES user (id_user) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE plante ADD CONSTRAINT FK_517A694788D30FF2 FOREIGN KEY (id_ferme) REFERENCES ferme (id_ferme)');
        $this->addSql('ALTER TABLE user_log ADD CONSTRAINT FK_6429094EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id_user)');
        $this->addSql('ALTER TABLE user_log ADD CONSTRAINT FK_6429094E99EB8EA2 FOREIGN KEY (performed_by) REFERENCES user (id_user)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE animal DROP FOREIGN KEY FK_6AAB231F88D30FF2');
        $this->addSql('ALTER TABLE erp_liste_achat DROP FOREIGN KEY FK_BA5D78AC82CC566');
        $this->addSql('ALTER TABLE erp_liste_achat DROP FOREIGN KEY FK_BA5D78A3F0033A2');
        $this->addSql('ALTER TABLE erp_liste_vente DROP FOREIGN KEY FK_A5867990660F6B7C');
        $this->addSql('ALTER TABLE erp_liste_vente DROP FOREIGN KEY FK_A58679903F0033A2');
        $this->addSql('ALTER TABLE ferme DROP FOREIGN KEY FK_66564EC26B3CA4B');
        $this->addSql('ALTER TABLE plante DROP FOREIGN KEY FK_517A694788D30FF2');
        $this->addSql('ALTER TABLE user_log DROP FOREIGN KEY FK_6429094EA76ED395');
        $this->addSql('ALTER TABLE user_log DROP FOREIGN KEY FK_6429094E99EB8EA2');
        $this->addSql('DROP TABLE animal');
        $this->addSql('DROP TABLE erp_achat');
        $this->addSql('DROP TABLE erp_liste_achat');
        $this->addSql('DROP TABLE erp_liste_vente');
        $this->addSql('DROP TABLE erp_service');
        $this->addSql('DROP TABLE erp_vente');
        $this->addSql('DROP TABLE ferme');
        $this->addSql('DROP TABLE plante');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_log');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
