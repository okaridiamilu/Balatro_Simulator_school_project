<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260215161837 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE joker_instance ADD CONSTRAINT FK_A6D464D7512323E8 FOREIGN KEY (joker_template_id) REFERENCES joker_template (id)');
        $this->addSql('ALTER TABLE joker_instance ADD CONSTRAINT FK_A6D464D7E075F7A4 FOREIGN KEY (partie_id) REFERENCES partie (id)');
        $this->addSql('ALTER TABLE partie ADD observatoire_actif TINYINT NOT NULL');
        $this->addSql('ALTER TABLE partie ADD CONSTRAINT FK_59B1F3DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE partie ADD CONSTRAINT FK_59B1F3DA5A56C8B FOREIGN KEY (hand_level_id) REFERENCES hand_level (id)');
        $this->addSql('ALTER TABLE partie ADD CONSTRAINT FK_59B1F3D28AA1B6F FOREIGN KEY (voucher_id) REFERENCES voucher (id)');
        $this->addSql('ALTER TABLE partie_carte ADD CONSTRAINT FK_632A30FE075F7A4 FOREIGN KEY (partie_id) REFERENCES partie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE partie_carte ADD CONSTRAINT FK_632A30FC9C7CEB6 FOREIGN KEY (carte_id) REFERENCES carte (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE partie_consommable ADD CONSTRAINT FK_3AA5FC05E075F7A4 FOREIGN KEY (partie_id) REFERENCES partie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE partie_consommable ADD CONSTRAINT FK_3AA5FC05C9CEB381 FOREIGN KEY (consommable_id) REFERENCES consommable (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D4A5A56C8B FOREIGN KEY (hand_level_id) REFERENCES hand_level (id)');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D428AA1B6F FOREIGN KEY (voucher_id) REFERENCES voucher (id)');
        $this->addSql('ALTER TABLE session_carte ADD CONSTRAINT FK_61A9472B613FECDF FOREIGN KEY (session_id) REFERENCES session (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE session_carte ADD CONSTRAINT FK_61A9472BC9C7CEB6 FOREIGN KEY (carte_id) REFERENCES carte (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE session_joker ADD CONSTRAINT FK_4F9B6C41613FECDF FOREIGN KEY (session_id) REFERENCES session (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE session_joker ADD CONSTRAINT FK_4F9B6C4132202C87 FOREIGN KEY (joker_id) REFERENCES joker (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE session_consommable ADD CONSTRAINT FK_5C15613A613FECDF FOREIGN KEY (session_id) REFERENCES session (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE session_consommable ADD CONSTRAINT FK_5C15613AC9CEB381 FOREIGN KEY (consommable_id) REFERENCES consommable (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE joker_instance DROP FOREIGN KEY FK_A6D464D7512323E8');
        $this->addSql('ALTER TABLE joker_instance DROP FOREIGN KEY FK_A6D464D7E075F7A4');
        $this->addSql('ALTER TABLE partie DROP FOREIGN KEY FK_59B1F3DA76ED395');
        $this->addSql('ALTER TABLE partie DROP FOREIGN KEY FK_59B1F3DA5A56C8B');
        $this->addSql('ALTER TABLE partie DROP FOREIGN KEY FK_59B1F3D28AA1B6F');
        $this->addSql('ALTER TABLE partie DROP observatoire_actif');
        $this->addSql('ALTER TABLE partie_carte DROP FOREIGN KEY FK_632A30FE075F7A4');
        $this->addSql('ALTER TABLE partie_carte DROP FOREIGN KEY FK_632A30FC9C7CEB6');
        $this->addSql('ALTER TABLE partie_consommable DROP FOREIGN KEY FK_3AA5FC05E075F7A4');
        $this->addSql('ALTER TABLE partie_consommable DROP FOREIGN KEY FK_3AA5FC05C9CEB381');
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D4A76ED395');
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D4A5A56C8B');
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D428AA1B6F');
        $this->addSql('ALTER TABLE session_carte DROP FOREIGN KEY FK_61A9472B613FECDF');
        $this->addSql('ALTER TABLE session_carte DROP FOREIGN KEY FK_61A9472BC9C7CEB6');
        $this->addSql('ALTER TABLE session_consommable DROP FOREIGN KEY FK_5C15613A613FECDF');
        $this->addSql('ALTER TABLE session_consommable DROP FOREIGN KEY FK_5C15613AC9CEB381');
        $this->addSql('ALTER TABLE session_joker DROP FOREIGN KEY FK_4F9B6C41613FECDF');
        $this->addSql('ALTER TABLE session_joker DROP FOREIGN KEY FK_4F9B6C4132202C87');
    }
}
