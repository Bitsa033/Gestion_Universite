<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220115212157 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notes_etudiant (id INT AUTO_INCREMENT NOT NULL, inscription_id INT DEFAULT NULL, ue_id INT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_1D7861425DAC5993 (inscription_id), INDEX IDX_1D78614262E883B1 (ue_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE notes_etudiant ADD CONSTRAINT FK_1D7861425DAC5993 FOREIGN KEY (inscription_id) REFERENCES inscription (id)');
        $this->addSql('ALTER TABLE notes_etudiant ADD CONSTRAINT FK_1D78614262E883B1 FOREIGN KEY (ue_id) REFERENCES ue (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE notes_etudiant');
    }
}
