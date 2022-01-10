<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220110140255 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE etudiant DROP FOREIGN KEY FK_717E22E3180AA129');
        $this->addSql('DROP INDEX IDX_717E22E3180AA129 ON etudiant');
        $this->addSql('ALTER TABLE etudiant DROP filiere_id');
        $this->addSql('ALTER TABLE inscription ADD filiere_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D6180AA129 FOREIGN KEY (filiere_id) REFERENCES filiere (id)');
        $this->addSql('CREATE INDEX IDX_5E90F6D6180AA129 ON inscription (filiere_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE etudiant ADD filiere_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE etudiant ADD CONSTRAINT FK_717E22E3180AA129 FOREIGN KEY (filiere_id) REFERENCES filiere (id)');
        $this->addSql('CREATE INDEX IDX_717E22E3180AA129 ON etudiant (filiere_id)');
        $this->addSql('ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D6180AA129');
        $this->addSql('DROP INDEX IDX_5E90F6D6180AA129 ON inscription');
        $this->addSql('ALTER TABLE inscription DROP filiere_id');
    }
}
