<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260214203224 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE carte (id INT AUTO_INCREMENT NOT NULL, number VARCHAR(255) NOT NULL, color VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, seal VARCHAR(255) NOT NULL, matter VARCHAR(255) NOT NULL, image VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE consommable (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, category VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, image VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE hand_level (id INT AUTO_INCREMENT NOT NULL, pair INT NOT NULL, two_pair INT NOT NULL, three_of_akind INT NOT NULL, straight INT NOT NULL, flush INT NOT NULL, full_house INT NOT NULL, four_of_akind INT NOT NULL, straight_flush INT NOT NULL, royal_flush INT NOT NULL, high_card INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE joker (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, etat VARCHAR(255) NOT NULL, rarete VARCHAR(255) NOT NULL, description VARCHAR(500) NOT NULL, image VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE joker_instance (id INT AUTO_INCREMENT NOT NULL, etat VARCHAR(255) DEFAULT NULL, ordre INT NOT NULL, compteur_stack INT NOT NULL, joker_template_id INT NOT NULL, partie_id INT NOT NULL, INDEX IDX_A6D464D7512323E8 (joker_template_id), INDEX IDX_A6D464D7E075F7A4 (partie_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE joker_template (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, rarete VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, image VARCHAR(255) DEFAULT NULL, effet_code VARCHAR(50) NOT NULL, condition_activation JSON DEFAULT NULL, type_stack VARCHAR(255) NOT NULL, stack_par_unite DOUBLE PRECISION NOT NULL, UNIQUE INDEX UNIQ_7384CA8A6C6E55B5 (nom), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE partie (id INT AUTO_INCREMENT NOT NULL, identifiant VARCHAR(100) NOT NULL, money INT NOT NULL, hand INT NOT NULL, discard INT NOT NULL, user_id INT NOT NULL, hand_level_id INT NOT NULL, voucher_id INT DEFAULT NULL, INDEX IDX_59B1F3DA76ED395 (user_id), UNIQUE INDEX UNIQ_59B1F3DA5A56C8B (hand_level_id), UNIQUE INDEX UNIQ_59B1F3D28AA1B6F (voucher_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE partie_carte (partie_id INT NOT NULL, carte_id INT NOT NULL, INDEX IDX_632A30FE075F7A4 (partie_id), INDEX IDX_632A30FC9C7CEB6 (carte_id), PRIMARY KEY (partie_id, carte_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE partie_consommable (partie_id INT NOT NULL, consommable_id INT NOT NULL, INDEX IDX_3AA5FC05E075F7A4 (partie_id), INDEX IDX_3AA5FC05C9CEB381 (consommable_id), PRIMARY KEY (partie_id, consommable_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE session (id INT AUTO_INCREMENT NOT NULL, identifiant VARCHAR(100) NOT NULL, money INT NOT NULL, hand INT NOT NULL, discard INT NOT NULL, user_id INT NOT NULL, hand_level_id INT NOT NULL, voucher_id INT DEFAULT NULL, INDEX IDX_D044D5D4A76ED395 (user_id), UNIQUE INDEX UNIQ_D044D5D4A5A56C8B (hand_level_id), UNIQUE INDEX UNIQ_D044D5D428AA1B6F (voucher_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE session_carte (session_id INT NOT NULL, carte_id INT NOT NULL, INDEX IDX_61A9472B613FECDF (session_id), INDEX IDX_61A9472BC9C7CEB6 (carte_id), PRIMARY KEY (session_id, carte_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE session_joker (session_id INT NOT NULL, joker_id INT NOT NULL, INDEX IDX_4F9B6C41613FECDF (session_id), INDEX IDX_4F9B6C4132202C87 (joker_id), PRIMARY KEY (session_id, joker_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE session_consommable (session_id INT NOT NULL, consommable_id INT NOT NULL, INDEX IDX_5C15613A613FECDF (session_id), INDEX IDX_5C15613AC9CEB381 (consommable_id), PRIMARY KEY (session_id, consommable_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(50) NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE voucher (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, image VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE y (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE joker_instance ADD CONSTRAINT FK_A6D464D7512323E8 FOREIGN KEY (joker_template_id) REFERENCES joker_template (id)');
        $this->addSql('ALTER TABLE joker_instance ADD CONSTRAINT FK_A6D464D7E075F7A4 FOREIGN KEY (partie_id) REFERENCES partie (id)');
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
        $this->addSql('ALTER TABLE partie_carte DROP FOREIGN KEY FK_632A30FE075F7A4');
        $this->addSql('ALTER TABLE partie_carte DROP FOREIGN KEY FK_632A30FC9C7CEB6');
        $this->addSql('ALTER TABLE partie_consommable DROP FOREIGN KEY FK_3AA5FC05E075F7A4');
        $this->addSql('ALTER TABLE partie_consommable DROP FOREIGN KEY FK_3AA5FC05C9CEB381');
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D4A76ED395');
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D4A5A56C8B');
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D428AA1B6F');
        $this->addSql('ALTER TABLE session_carte DROP FOREIGN KEY FK_61A9472B613FECDF');
        $this->addSql('ALTER TABLE session_carte DROP FOREIGN KEY FK_61A9472BC9C7CEB6');
        $this->addSql('ALTER TABLE session_joker DROP FOREIGN KEY FK_4F9B6C41613FECDF');
        $this->addSql('ALTER TABLE session_joker DROP FOREIGN KEY FK_4F9B6C4132202C87');
        $this->addSql('ALTER TABLE session_consommable DROP FOREIGN KEY FK_5C15613A613FECDF');
        $this->addSql('ALTER TABLE session_consommable DROP FOREIGN KEY FK_5C15613AC9CEB381');
        $this->addSql('DROP TABLE carte');
        $this->addSql('DROP TABLE consommable');
        $this->addSql('DROP TABLE hand_level');
        $this->addSql('DROP TABLE joker');
        $this->addSql('DROP TABLE joker_instance');
        $this->addSql('DROP TABLE joker_template');
        $this->addSql('DROP TABLE partie');
        $this->addSql('DROP TABLE partie_carte');
        $this->addSql('DROP TABLE partie_consommable');
        $this->addSql('DROP TABLE session');
        $this->addSql('DROP TABLE session_carte');
        $this->addSql('DROP TABLE session_joker');
        $this->addSql('DROP TABLE session_consommable');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE voucher');
        $this->addSql('DROP TABLE y');
    }
}
