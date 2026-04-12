<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260412083657 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE analyse DROP FOREIGN KEY FK_351B0C7E33A2AC9B');
        $this->addSql('ALTER TABLE analyse ADD statut VARCHAR(20) NOT NULL DEFAULT \'en_attente\', ADD description_demande LONGTEXT DEFAULT NULL, ADD id_demandeur INT DEFAULT NULL, ADD id_animal_cible INT DEFAULT NULL, ADD id_plante_cible INT DEFAULT NULL, CHANGE id_technicien id_technicien INT DEFAULT NULL');
        $this->addSql('ALTER TABLE analyse ADD CONSTRAINT FK_351B0C7EE6681A34 FOREIGN KEY (id_demandeur) REFERENCES user (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE analyse ADD CONSTRAINT FK_351B0C7EE36F40BD FOREIGN KEY (id_animal_cible) REFERENCES animal (id_animal) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE analyse ADD CONSTRAINT FK_351B0C7EA7275E42 FOREIGN KEY (id_plante_cible) REFERENCES plante (id_plante) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE analyse ADD CONSTRAINT FK_351B0C7E33A2AC9B FOREIGN KEY (id_technicien) REFERENCES user (id_user) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_351B0C7EE6681A34 ON analyse (id_demandeur)');
        $this->addSql('CREATE INDEX IDX_351B0C7EE36F40BD ON analyse (id_animal_cible)');
        $this->addSql('CREATE INDEX IDX_351B0C7EA7275E42 ON analyse (id_plante_cible)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE analyse DROP FOREIGN KEY FK_351B0C7EE6681A34');
        $this->addSql('ALTER TABLE analyse DROP FOREIGN KEY FK_351B0C7EE36F40BD');
        $this->addSql('ALTER TABLE analyse DROP FOREIGN KEY FK_351B0C7EA7275E42');
        $this->addSql('ALTER TABLE analyse DROP FOREIGN KEY FK_351B0C7E33A2AC9B');
        $this->addSql('DROP INDEX IDX_351B0C7EE6681A34 ON analyse');
        $this->addSql('DROP INDEX IDX_351B0C7EE36F40BD ON analyse');
        $this->addSql('DROP INDEX IDX_351B0C7EA7275E42 ON analyse');
        $this->addSql('ALTER TABLE analyse DROP statut, DROP description_demande, DROP id_demandeur, DROP id_animal_cible, DROP id_plante_cible, CHANGE id_technicien id_technicien INT NOT NULL');
        $this->addSql('ALTER TABLE analyse ADD CONSTRAINT FK_351B0C7E33A2AC9B FOREIGN KEY (id_technicien) REFERENCES user (id_user) ON DELETE CASCADE');
    }
}
