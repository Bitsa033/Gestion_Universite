<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211231183018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ue ADD matiere_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ue ADD CONSTRAINT FK_2E490A9BF46CD258 FOREIGN KEY (matiere_id) REFERENCES matiere (id)');
        $this->addSql('CREATE INDEX IDX_2E490A9BF46CD258 ON ue (matiere_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ue DROP FOREIGN KEY FK_2E490A9BF46CD258');
        $this->addSql('DROP INDEX IDX_2E490A9BF46CD258 ON ue');
        $this->addSql('ALTER TABLE ue DROP matiere_id');
    }
}
