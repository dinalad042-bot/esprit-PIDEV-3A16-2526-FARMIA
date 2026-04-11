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
        // 1. Créer la table user_face
        $this->addSql('
            CREATE TABLE user_face (
                id               INT AUTO_INCREMENT NOT NULL,
                user_id          INT NOT NULL,
                image_path       VARCHAR(500)       DEFAULT NULL,
                samples_count    INT                NOT NULL DEFAULT 0,
                is_active        TINYINT(1)         NOT NULL DEFAULT 1,
                confidence_score DOUBLE PRECISION   DEFAULT NULL,
                enrolled_at      DATETIME           NOT NULL,
                updated_at       DATETIME           DEFAULT NULL,
                INDEX IDX_USER_FACE_USER (user_id),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        // 2. Ajouter la contrainte FK vers la table user
        $this->addSql('
            ALTER TABLE user_face
                ADD CONSTRAINT FK_USER_FACE_USER_ID
                FOREIGN KEY (user_id) REFERENCES user (id_user)
                ON DELETE CASCADE
        ');

        // 3. Supprimer les anciens champs biométriques de la table user
        //    (ils sont maintenant gérés via UserFace)
        $this->addSql('
            ALTER TABLE user
                DROP COLUMN IF EXISTS face_descriptor,
                DROP COLUMN IF EXISTS face_auth_enabled,
                DROP COLUMN IF EXISTS face_registered_at
        ');
    }

    public function down(Schema $schema): void
    {
        // Remettre les anciens champs dans user
        $this->addSql('
            ALTER TABLE user
                ADD face_descriptor     LONGTEXT    DEFAULT NULL,
                ADD face_auth_enabled   TINYINT(1)  NOT NULL DEFAULT 0,
                ADD face_registered_at  DATETIME    DEFAULT NULL
        ');

        // Supprimer la contrainte FK puis la table
        $this->addSql('ALTER TABLE user_face DROP FOREIGN KEY FK_USER_FACE_USER_ID');
        $this->addSql('DROP TABLE user_face');
    }
}
