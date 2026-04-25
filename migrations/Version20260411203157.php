<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260411203157 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // SQLite: use table recreation for column modifications
        $this->addSql('ALTER TABLE user ADD reset_code VARCHAR(6) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD reset_code_expires_at DATETIME DEFAULT NULL');
        // Recreate user table to modify cin (VARCHAR 20 -> 8) and telephone (VARCHAR 20 -> 8)
        $this->addSql('CREATE TABLE user_new (id_user INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(100) DEFAULT NULL, prenom VARCHAR(100) DEFAULT NULL, email VARCHAR(150) NOT NULL, password VARCHAR(255) NOT NULL, cin VARCHAR(8) DEFAULT NULL, adresse LONGTEXT DEFAULT NULL, telephone VARCHAR(8) DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, role VARCHAR(50) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, reset_code VARCHAR(6) DEFAULT NULL, reset_code_expires_at DATETIME DEFAULT NULL)');
        $this->addSql('INSERT INTO user_new (id_user, nom, prenom, email, password, cin, adresse, telephone, image_url, role, created_at, updated_at) SELECT id_user, nom, prenom, email, password, cin, adresse, telephone, image_url, role, created_at, updated_at FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('ALTER TABLE user_new RENAME TO user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649ABE530DA ON user (cin)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // SQLite: use table recreation for column modifications
        // Recreate user table to restore cin (VARCHAR 8 -> 20) and telephone (VARCHAR 8 -> 20)
        // and remove reset_code columns
        $this->addSql('CREATE TABLE user_new (id_user INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(100) DEFAULT NULL, prenom VARCHAR(100) DEFAULT NULL, email VARCHAR(150) NOT NULL, password VARCHAR(255) NOT NULL, cin VARCHAR(20) DEFAULT NULL, adresse LONGTEXT DEFAULT NULL, telephone VARCHAR(20) DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, role VARCHAR(50) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL)');
        $this->addSql('INSERT INTO user_new (id_user, nom, prenom, email, password, cin, adresse, telephone, image_url, role, created_at, updated_at) SELECT id_user, nom, prenom, email, password, cin, adresse, telephone, image_url, role, created_at, updated_at FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('ALTER TABLE user_new RENAME TO user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649ABE530DA ON user (cin)');
    }
}
