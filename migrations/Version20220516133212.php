<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220516133212 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE filiere CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE semestre ADD user_id INT NOT NULL, ADD created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE semestre ADD CONSTRAINT FK_71688FBCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_71688FBCA76ED395 ON semestre (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE filiere CHANGE user_id user_id INT DEFAULT 1');
        $this->addSql('ALTER TABLE semestre DROP FOREIGN KEY FK_71688FBCA76ED395');
        $this->addSql('DROP INDEX IDX_71688FBCA76ED395 ON semestre');
        $this->addSql('ALTER TABLE semestre DROP user_id, DROP created_at');
    }
}
