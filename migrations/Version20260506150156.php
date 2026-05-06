<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260506150156 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE analyse DROP FOREIGN KEY FK_351B0C7E33A2AC9B');
        $this->addSql('ALTER TABLE analyse DROP FOREIGN KEY FK_351B0C7E88D30FF2');
        $this->addSql('DROP INDEX IDX_351B0C7E33A2AC9B ON analyse');
        $this->addSql('DROP INDEX IDX_351B0C7E88D30FF2 ON analyse');
        $this->addSql('ALTER TABLE analyse CHANGE id_technicien id_technicien_id INT DEFAULT NULL, CHANGE id_ferme id_ferme_id INT NOT NULL');
        $this->addSql('ALTER TABLE analyse ADD CONSTRAINT FK_351B0C7EAD6DA333 FOREIGN KEY (id_technicien_id) REFERENCES user (id_user) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE analyse ADD CONSTRAINT FK_351B0C7E4843FDA7 FOREIGN KEY (id_ferme_id) REFERENCES ferme (id_ferme) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_351B0C7EAD6DA333 ON analyse (id_technicien_id)');
        $this->addSql('CREATE INDEX IDX_351B0C7E4843FDA7 ON analyse (id_ferme_id)');
        $this->addSql('ALTER TABLE erp_achat CHANGE total total NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE erp_matiere CHANGE prix_unitaire prix_unitaire NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL');
        $this->addSql('ALTER TABLE erp_vente CHANGE total total NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE ferme DROP created_at, DROP updated_at, CHANGE surface surface DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE suivi_sante DROP FOREIGN KEY FK_6C0412484C9C96F2');
        $this->addSql('DROP INDEX IDX_6C0412484C9C96F2 ON suivi_sante');
        $this->addSql('ALTER TABLE suivi_sante ADD performed_by_id INT DEFAULT NULL, CHANGE id_animal animal_id INT NOT NULL');
        $this->addSql('ALTER TABLE suivi_sante ADD CONSTRAINT FK_6C0412488E962C16 FOREIGN KEY (animal_id) REFERENCES animal (id_animal) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE suivi_sante ADD CONSTRAINT FK_6C0412482E65C292 FOREIGN KEY (performed_by_id) REFERENCES user (id_user)');
        $this->addSql('CREATE INDEX IDX_6C0412488E962C16 ON suivi_sante (animal_id)');
        $this->addSql('CREATE INDEX IDX_6C0412482E65C292 ON suivi_sante (performed_by_id)');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE available_at available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE analyse DROP FOREIGN KEY FK_351B0C7EAD6DA333');
        $this->addSql('ALTER TABLE analyse DROP FOREIGN KEY FK_351B0C7E4843FDA7');
        $this->addSql('DROP INDEX IDX_351B0C7EAD6DA333 ON analyse');
        $this->addSql('DROP INDEX IDX_351B0C7E4843FDA7 ON analyse');
        $this->addSql('ALTER TABLE analyse CHANGE id_technicien_id id_technicien INT DEFAULT NULL, CHANGE id_ferme_id id_ferme INT NOT NULL');
        $this->addSql('ALTER TABLE analyse ADD CONSTRAINT FK_351B0C7E33A2AC9B FOREIGN KEY (id_technicien) REFERENCES user (id_user) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE analyse ADD CONSTRAINT FK_351B0C7E88D30FF2 FOREIGN KEY (id_ferme) REFERENCES ferme (id_ferme) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_351B0C7E33A2AC9B ON analyse (id_technicien)');
        $this->addSql('CREATE INDEX IDX_351B0C7E88D30FF2 ON analyse (id_ferme)');
        $this->addSql('ALTER TABLE erp_achat CHANGE total total DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE erp_matiere CHANGE prix_unitaire prix_unitaire DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE erp_vente CHANGE total total DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE ferme ADD created_at DATETIME DEFAULT NULL, ADD updated_at DATETIME DEFAULT NULL, CHANGE surface surface DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL, CHANGE available_at available_at DATETIME NOT NULL, CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE suivi_sante DROP FOREIGN KEY FK_6C0412488E962C16');
        $this->addSql('ALTER TABLE suivi_sante DROP FOREIGN KEY FK_6C0412482E65C292');
        $this->addSql('DROP INDEX IDX_6C0412488E962C16 ON suivi_sante');
        $this->addSql('DROP INDEX IDX_6C0412482E65C292 ON suivi_sante');
        $this->addSql('ALTER TABLE suivi_sante DROP performed_by_id, CHANGE animal_id id_animal INT NOT NULL');
        $this->addSql('ALTER TABLE suivi_sante ADD CONSTRAINT FK_6C0412484C9C96F2 FOREIGN KEY (id_animal) REFERENCES animal (id_animal) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_6C0412484C9C96F2 ON suivi_sante (id_animal)');
    }
}
