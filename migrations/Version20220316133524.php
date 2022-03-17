<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220316133524 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notes_etudiant ADD semestre_id INT NOT NULL');
        $this->addSql('ALTER TABLE notes_etudiant ADD CONSTRAINT FK_1D7861425577AFDB FOREIGN KEY (semestre_id) REFERENCES semestre (id)');
        $this->addSql('CREATE INDEX IDX_1D7861425577AFDB ON notes_etudiant (semestre_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notes_etudiant DROP FOREIGN KEY FK_1D7861425577AFDB');
        $this->addSql('DROP INDEX IDX_1D7861425577AFDB ON notes_etudiant');
        $this->addSql('ALTER TABLE notes_etudiant DROP semestre_id');
    }
}
