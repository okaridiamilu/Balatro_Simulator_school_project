<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour supprimer les tables obsolètes:
 * - y (duplicate User entity)
 * - joker (ancien système remplacé par joker_template/joker_instance)
 * - session et ses tables de liaison (système parallèle inutilisé)
 */
final class Version20260217000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop obsolete tables: y, joker, session, session_carte, session_joker, session_consommable';
    }

    public function up(Schema $schema): void
    {
        // Drop join tables first (foreign key constraints)
        $this->addSql('DROP TABLE IF EXISTS session_consommable');
        $this->addSql('DROP TABLE IF EXISTS session_joker');
        $this->addSql('DROP TABLE IF EXISTS session_carte');
        
        // Drop main tables
        $this->addSql('DROP TABLE IF EXISTS session');
        $this->addSql('DROP TABLE IF EXISTS joker');
        $this->addSql('DROP TABLE IF EXISTS y');
    }

    public function down(Schema $schema): void
    {
        // No rollback possible - tables and data would be lost
        $this->throwIrreversibleMigrationException();
    }
}
