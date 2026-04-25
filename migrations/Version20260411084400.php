<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration: Création de la table user_face et suppression des colonnes
 * face biométriques directement dans la table user.
 *
 * Avant : les données faciales étaient stockées directement dans user
 *         (face_descriptor, face_auth_enabled, face_registered_at).
 *
 * Après : entité dédiée UserFace avec relation ManyToOne vers User.
 *         Cela permet de gérer plusieurs enrôlements, l'historique,
 *         le statut actif/inactif, et les métadonnées de confiance.
 */
final class Version20260411084400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crée la table user_face et supprime les anciens champs biométriques de la table user.';
    }

    public function up(Schema $schema): void
    {
        // 1. Créer la table user_face avec FK inline (SQLite compatible)
        $this->addSql('CREATE TABLE user_face (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INT NOT NULL, image_path VARCHAR(500) DEFAULT NULL, samples_count INT NOT NULL DEFAULT 0, is_active TINYINT(1) NOT NULL DEFAULT 1, confidence_score DOUBLE PRECISION DEFAULT NULL, enrolled_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, CONSTRAINT FK_USER_FACE_USER_ID FOREIGN KEY (user_id) REFERENCES user (id_user) ON DELETE CASCADE)');
        $this->addSql('CREATE INDEX IDX_USER_FACE_USER ON user_face (user_id)');
        // 2. Supprimer les anciens champs biométriques de la table user
        //    SQLite ne supporte pas DROP COLUMN, on utilise la méthode de recréation de table
        $this->addSql('CREATE TABLE user_new (id_user INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(100) DEFAULT NULL, prenom VARCHAR(100) DEFAULT NULL, email VARCHAR(150) NOT NULL, password VARCHAR(255) NOT NULL, cin VARCHAR(20) DEFAULT NULL, adresse LONGTEXT DEFAULT NULL, telephone VARCHAR(20) DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, role VARCHAR(50) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL)');
        $this->addSql('INSERT INTO user_new (id_user, nom, prenom, email, password, cin, adresse, telephone, image_url, role, created_at, updated_at) SELECT id_user, nom, prenom, email, password, cin, adresse, telephone, image_url, role, created_at, updated_at FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('ALTER TABLE user_new RENAME TO user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649ABE530DA ON user (cin)');
    }

    public function down(Schema $schema): void
    {
        // Remettre les anciens champs dans user (SQLite: recréation de table)
        $this->addSql('CREATE TABLE user_new (id_user INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(100) DEFAULT NULL, prenom VARCHAR(100) DEFAULT NULL, email VARCHAR(150) NOT NULL, password VARCHAR(255) NOT NULL, cin VARCHAR(20) DEFAULT NULL, adresse LONGTEXT DEFAULT NULL, telephone VARCHAR(20) DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, role VARCHAR(50) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, face_descriptor LONGTEXT DEFAULT NULL, face_auth_enabled TINYINT(1) NOT NULL DEFAULT 0, face_registered_at DATETIME DEFAULT NULL)');
        $this->addSql('INSERT INTO user_new (id_user, nom, prenom, email, password, cin, adresse, telephone, image_url, role, created_at, updated_at) SELECT id_user, nom, prenom, email, password, cin, adresse, telephone, image_url, role, created_at, updated_at FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('ALTER TABLE user_new RENAME TO user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649ABE530DA ON user (cin)');
        // Supprimer la table user_face
        $this->addSql('DROP TABLE user_face');
    }
}
