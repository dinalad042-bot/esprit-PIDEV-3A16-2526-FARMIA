<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260424214746 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // SQLite: arrosage and suivi_sante already exist from earlier migrations
        // Recreate analyse without diagnosis_mode (weather columns already added by Version20260423084644)
        $this->addSql('CREATE TABLE analyse_new (id_analyse INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, date_analyse DATETIME DEFAULT NULL, resultat_technique LONGTEXT DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, id_technicien INT DEFAULT NULL, id_ferme INT NOT NULL, id_user INT NOT NULL, weather_data JSON DEFAULT NULL, weather_fetched_at DATETIME DEFAULT NULL, statut VARCHAR(20) NOT NULL, description_demande LONGTEXT DEFAULT NULL, id_demandeur INT NOT NULL, id_animal_cible INT DEFAULT NULL, id_plante_cible INT DEFAULT NULL, ai_diagnosis_result LONGTEXT DEFAULT NULL, ai_diagnosis_date DATETIME DEFAULT NULL, ai_confidence_score VARCHAR(20) DEFAULT NULL, CONSTRAINT FK_33A2AC9B33A2AC9B FOREIGN KEY (id_technicien) REFERENCES user (id_user), CONSTRAINT FK_33A2AC9B88D30FF2 FOREIGN KEY (id_ferme) REFERENCES ferme (id_ferme), CONSTRAINT FK_33A2AC9BFE6E88D7 FOREIGN KEY (id_user) REFERENCES user (id_user), CONSTRAINT FK_33A2AC9B6B3CA4B FOREIGN KEY (id_demandeur) REFERENCES user (id_user), CONSTRAINT FK_33A2AC9B65A2C0C8 FOREIGN KEY (id_animal_cible) REFERENCES animal (id_animal), CONSTRAINT FK_33A2AC9B8E76A925 FOREIGN KEY (id_plante_cible) REFERENCES plante (id_plante))');
        $this->addSql('INSERT INTO analyse_new (id_analyse, date_analyse, resultat_technique, image_url, id_technicien, id_ferme, id_user, weather_data, weather_fetched_at, statut, description_demande, id_demandeur, id_animal_cible, id_plante_cible, ai_diagnosis_result, ai_diagnosis_date, ai_confidence_score) SELECT id_analyse, date_analyse, resultat_technique, image_url, id_technicien, id_ferme, (SELECT id_user FROM user LIMIT 1), weather_data, weather_fetched_at, statut, description_demande, (SELECT id_user FROM user LIMIT 1), id_animal_cible, id_plante_cible, ai_diagnosis_result, ai_diagnosis_date, ai_confidence_score FROM analyse');
        $this->addSql('DROP TABLE analyse');
        $this->addSql('ALTER TABLE analyse_new RENAME TO analyse');
        $this->addSql('CREATE INDEX IDX_351B0C7E33A2AC9B ON analyse (id_technicien)');
        $this->addSql('CREATE INDEX IDX_351B0C7E88D30FF2 ON analyse (id_ferme)');
        $this->addSql('CREATE INDEX IDX_12B455B34C9C96F2 ON analyse (id_animal_cible)');
        $this->addSql('CREATE INDEX IDX_12B455B3FE6E88D7 ON analyse (id_user)');
        // Recreate ferme with surface as DOUBLE PRECISION, without created_at/updated_at
        $this->addSql('CREATE TABLE ferme_new (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, localisation VARCHAR(255) NOT NULL, surface DOUBLE PRECISION NOT NULL, id_user INT NOT NULL, CONSTRAINT FK_425CF976FE6E88D7 FOREIGN KEY (id_user) REFERENCES user (id_user))');
        $this->addSql('INSERT INTO ferme_new (id, nom, localisation, surface, id_user) SELECT id, nom_ferme, lieu, surface, id_user FROM ferme');
        $this->addSql('DROP TABLE ferme');
        $this->addSql('ALTER TABLE ferme_new RENAME TO ferme');
        $this->addSql('CREATE INDEX IDX_425CF976FE6E88D7 ON ferme (id_user)');
    }

    public function down(Schema $schema): void
    {
        // SQLite: recreate notification table
        $this->addSql('CREATE TABLE notification (id_notification INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, message LONGTEXT NOT NULL, type VARCHAR(50) NOT NULL, is_read INTEGER NOT NULL, link VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, id_user INT NOT NULL, CONSTRAINT FK_BF5476CA6B3CA4B FOREIGN KEY (id_user) REFERENCES user (id_user) ON DELETE CASCADE)');
        $this->addSql('CREATE INDEX IDX_BF5476CA6B3CA4B ON notification (id_user)');
        // Recreate analyse with diagnosis_mode, without weather columns
        $this->addSql('CREATE TABLE analyse_new (id_analyse INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, date_analyse DATETIME DEFAULT NULL, resultat_technique LONGTEXT DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, id_technicien INT DEFAULT NULL, id_ferme INT NOT NULL, id_user INT NOT NULL, diagnosis_mode VARCHAR(20) DEFAULT NULL, statut VARCHAR(20) NOT NULL, description_demande LONGTEXT DEFAULT NULL, id_demandeur INT NOT NULL, id_animal_cible INT DEFAULT NULL, id_plante_cible INT DEFAULT NULL, ai_diagnosis_result LONGTEXT DEFAULT NULL, ai_diagnosis_date DATETIME DEFAULT NULL, ai_confidence_score VARCHAR(20) DEFAULT NULL, CONSTRAINT FK_33A2AC9B33A2AC9B FOREIGN KEY (id_technicien) REFERENCES user (id_user), CONSTRAINT FK_33A2AC9B88D30FF2 FOREIGN KEY (id_ferme) REFERENCES ferme (id_ferme), CONSTRAINT FK_33A2AC9BFE6E88D7 FOREIGN KEY (id_user) REFERENCES user (id_user), CONSTRAINT FK_33A2AC9B6B3CA4B FOREIGN KEY (id_demandeur) REFERENCES user (id_user), CONSTRAINT FK_33A2AC9B65A2C0C8 FOREIGN KEY (id_animal_cible) REFERENCES animal (id_animal), CONSTRAINT FK_33A2AC9B8E76A925 FOREIGN KEY (id_plante_cible) REFERENCES plante (id_plante))');
        $this->addSql('INSERT INTO analyse_new (id_analyse, date_analyse, resultat_technique, image_url, id_technicien, id_ferme, id_user, diagnosis_mode, statut, description_demande, id_demandeur, id_animal_cible, id_plante_cible, ai_diagnosis_result, ai_diagnosis_date, ai_confidence_score) SELECT id_analyse, date_analyse, resultat_technique, image_url, id_technicien, id_ferme, (SELECT id_user FROM user LIMIT 1), diagnosis_mode, statut, description_demande, (SELECT id_user FROM user LIMIT 1), id_animal_cible, id_plante_cible, ai_diagnosis_result, ai_diagnosis_date, ai_confidence_score FROM analyse');
        $this->addSql('DROP TABLE analyse');
        $this->addSql('ALTER TABLE analyse_new RENAME TO analyse');
        $this->addSql('CREATE INDEX IDX_351B0C7E33A2AC9B ON analyse (id_technicien)');
        $this->addSql('CREATE INDEX IDX_351B0C7E88D30FF2 ON analyse (id_ferme)');
        $this->addSql('CREATE INDEX IDX_12B455B34C9C96F2 ON analyse (id_animal_cible)');
        $this->addSql('CREATE INDEX IDX_12B455B3FE6E88D7 ON analyse (id_user)');
        // Recreate ferme with created_at, updated_at, surface as DOUBLE PRECISION
        $this->addSql('CREATE TABLE ferme_new (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, localisation VARCHAR(255) NOT NULL, surface DOUBLE PRECISION NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, id_user INT NOT NULL, CONSTRAINT FK_425CF976FE6E88D7 FOREIGN KEY (id_user) REFERENCES user (id_user))');
        $this->addSql('INSERT INTO ferme_new (id, nom, localisation, surface, created_at, updated_at, id_user) SELECT id, nom_ferme, lieu, surface, created_at, updated_at, id_user FROM ferme');
        $this->addSql('DROP TABLE ferme');
        $this->addSql('ALTER TABLE ferme_new RENAME TO ferme');
        $this->addSql('CREATE INDEX IDX_425CF976FE6E88D7 ON ferme (id_user)');
    }
}
