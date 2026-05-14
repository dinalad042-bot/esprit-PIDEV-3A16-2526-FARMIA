<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260505184040 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE analyse DROP FOREIGN KEY FK_351B0C7E88D30FF2');
        $this->addSql('ALTER TABLE analyse DROP FOREIGN KEY FK_351B0C7EA7275E42');
        $this->addSql('ALTER TABLE analyse DROP FOREIGN KEY FK_351B0C7EE36F40BD');
        $this->addSql('DROP INDEX IDX_351B0C7EA7275E42 ON analyse');
        $this->addSql('DROP INDEX IDX_351B0C7E88D30FF2 ON analyse');
        $this->addSql('DROP INDEX IDX_351B0C7EE36F40BD ON analyse');
        $this->addSql('ALTER TABLE analyse ADD plante_id INT DEFAULT NULL, ADD animal_id INT DEFAULT NULL, DROP id_animal_cible, DROP id_plante_cible, CHANGE id_ferme ferme_id INT NOT NULL');
        $this->addSql('ALTER TABLE analyse ADD CONSTRAINT FK_351B0C7E18981132 FOREIGN KEY (ferme_id) REFERENCES ferme (id_ferme) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE analyse ADD CONSTRAINT FK_351B0C7E177B16E8 FOREIGN KEY (plante_id) REFERENCES plante (id_plante) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE analyse ADD CONSTRAINT FK_351B0C7E8E962C16 FOREIGN KEY (animal_id) REFERENCES animal (id_animal) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_351B0C7E18981132 ON analyse (ferme_id)');
        $this->addSql('CREATE INDEX IDX_351B0C7E177B16E8 ON analyse (plante_id)');
        $this->addSql('CREATE INDEX IDX_351B0C7E8E962C16 ON analyse (animal_id)');
        $this->addSql('ALTER TABLE animal DROP FOREIGN KEY FK_6AAB231F88D30FF2');
        $this->addSql('DROP INDEX IDX_6AAB231F88D30FF2 ON animal');
        $this->addSql('ALTER TABLE animal CHANGE id_ferme ferme_id INT NOT NULL');
        $this->addSql('ALTER TABLE animal ADD CONSTRAINT FK_6AAB231F18981132 FOREIGN KEY (ferme_id) REFERENCES ferme (id_ferme) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_6AAB231F18981132 ON animal (ferme_id)');
        $this->addSql('ALTER TABLE ferme DROP FOREIGN KEY FK_66564EC26B3CA4B');
        $this->addSql('DROP INDEX IDX_66564EC26B3CA4B ON ferme');
        $this->addSql('ALTER TABLE ferme DROP created_at, DROP updated_at, CHANGE surface surface DOUBLE PRECISION NOT NULL, CHANGE id_user user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ferme ADD CONSTRAINT FK_66564EC2A76ED395 FOREIGN KEY (user_id) REFERENCES user (id_user) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_66564EC2A76ED395 ON ferme (user_id)');
        $this->addSql('ALTER TABLE plante DROP FOREIGN KEY FK_517A694788D30FF2');
        $this->addSql('DROP INDEX IDX_517A694788D30FF2 ON plante');
        $this->addSql('ALTER TABLE plante CHANGE id_ferme ferme_id INT NOT NULL');
        $this->addSql('ALTER TABLE plante ADD CONSTRAINT FK_517A694718981132 FOREIGN KEY (ferme_id) REFERENCES ferme (id_ferme) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_517A694718981132 ON plante (ferme_id)');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE available_at available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE analyse DROP FOREIGN KEY FK_351B0C7E18981132');
        $this->addSql('ALTER TABLE analyse DROP FOREIGN KEY FK_351B0C7E177B16E8');
        $this->addSql('ALTER TABLE analyse DROP FOREIGN KEY FK_351B0C7E8E962C16');
        $this->addSql('DROP INDEX IDX_351B0C7E18981132 ON analyse');
        $this->addSql('DROP INDEX IDX_351B0C7E177B16E8 ON analyse');
        $this->addSql('DROP INDEX IDX_351B0C7E8E962C16 ON analyse');
        $this->addSql('ALTER TABLE analyse ADD id_animal_cible INT DEFAULT NULL, ADD id_plante_cible INT DEFAULT NULL, DROP plante_id, DROP animal_id, CHANGE ferme_id id_ferme INT NOT NULL');
        $this->addSql('ALTER TABLE analyse ADD CONSTRAINT FK_351B0C7E88D30FF2 FOREIGN KEY (id_ferme) REFERENCES ferme (id_ferme) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE analyse ADD CONSTRAINT FK_351B0C7EA7275E42 FOREIGN KEY (id_plante_cible) REFERENCES plante (id_plante) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE analyse ADD CONSTRAINT FK_351B0C7EE36F40BD FOREIGN KEY (id_animal_cible) REFERENCES animal (id_animal) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_351B0C7EA7275E42 ON analyse (id_plante_cible)');
        $this->addSql('CREATE INDEX IDX_351B0C7E88D30FF2 ON analyse (id_ferme)');
        $this->addSql('CREATE INDEX IDX_351B0C7EE36F40BD ON analyse (id_animal_cible)');
        $this->addSql('ALTER TABLE animal DROP FOREIGN KEY FK_6AAB231F18981132');
        $this->addSql('DROP INDEX IDX_6AAB231F18981132 ON animal');
        $this->addSql('ALTER TABLE animal CHANGE ferme_id id_ferme INT NOT NULL');
        $this->addSql('ALTER TABLE animal ADD CONSTRAINT FK_6AAB231F88D30FF2 FOREIGN KEY (id_ferme) REFERENCES ferme (id_ferme)');
        $this->addSql('CREATE INDEX IDX_6AAB231F88D30FF2 ON animal (id_ferme)');
        $this->addSql('ALTER TABLE ferme DROP FOREIGN KEY FK_66564EC2A76ED395');
        $this->addSql('DROP INDEX IDX_66564EC2A76ED395 ON ferme');
        $this->addSql('ALTER TABLE ferme ADD created_at DATETIME DEFAULT NULL, ADD updated_at DATETIME DEFAULT NULL, CHANGE surface surface DOUBLE PRECISION DEFAULT NULL, CHANGE user_id id_user INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ferme ADD CONSTRAINT FK_66564EC26B3CA4B FOREIGN KEY (id_user) REFERENCES user (id_user) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_66564EC26B3CA4B ON ferme (id_user)');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL, CHANGE available_at available_at DATETIME NOT NULL, CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE plante DROP FOREIGN KEY FK_517A694718981132');
        $this->addSql('DROP INDEX IDX_517A694718981132 ON plante');
        $this->addSql('ALTER TABLE plante CHANGE ferme_id id_ferme INT NOT NULL');
        $this->addSql('ALTER TABLE plante ADD CONSTRAINT FK_517A694788D30FF2 FOREIGN KEY (id_ferme) REFERENCES ferme (id_ferme)');
        $this->addSql('CREATE INDEX IDX_517A694788D30FF2 ON plante (id_ferme)');
    }
}
