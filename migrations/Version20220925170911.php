<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220925170911 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE etudiant (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, sexe VARCHAR(2) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_717E22E3A625945B (prenom), INDEX IDX_717E22E3A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE filiere (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, sigle VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_2ED05D9EA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE inscription (id INT AUTO_INCREMENT NOT NULL, etudiant_id INT DEFAULT NULL, filiere_id INT DEFAULT NULL, niveau_id INT DEFAULT NULL, user_id INT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_5E90F6D6DDEAB1A3 (etudiant_id), INDEX IDX_5E90F6D6180AA129 (filiere_id), INDEX IDX_5E90F6D6B3E9C81 (niveau_id), INDEX IDX_5E90F6D6A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE matiere (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_9014574AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE niveau (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_4BDFF36BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notes_etudiant (id INT AUTO_INCREMENT NOT NULL, inscription_id INT DEFAULT NULL, ue_id INT DEFAULT NULL, user_id INT DEFAULT NULL, semestre_id INT NOT NULL, created_at DATETIME NOT NULL, moyenne DOUBLE PRECISION NOT NULL, INDEX IDX_1D7861425DAC5993 (inscription_id), INDEX IDX_1D78614262E883B1 (ue_id), INDEX IDX_1D786142A76ED395 (user_id), INDEX IDX_1D7861425577AFDB (semestre_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE semestre (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, nom VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_71688FBCA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ue (id INT AUTO_INCREMENT NOT NULL, filiere_id INT DEFAULT NULL, niveau_id INT DEFAULT NULL, matiere_id INT DEFAULT NULL, user_id INT DEFAULT NULL, semestre_id INT DEFAULT NULL, created_at DATETIME NOT NULL, note DOUBLE PRECISION DEFAULT NULL, code VARCHAR(255) DEFAULT NULL, INDEX IDX_2E490A9B180AA129 (filiere_id), INDEX IDX_2E490A9BB3E9C81 (niveau_id), INDEX IDX_2E490A9BF46CD258 (matiere_id), INDEX IDX_2E490A9BA76ED395 (user_id), INDEX IDX_2E490A9B5577AFDB (semestre_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE etudiant ADD CONSTRAINT FK_717E22E3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE filiere ADD CONSTRAINT FK_2ED05D9EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D6DDEAB1A3 FOREIGN KEY (etudiant_id) REFERENCES etudiant (id)');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D6180AA129 FOREIGN KEY (filiere_id) REFERENCES filiere (id)');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D6B3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id)');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE matiere ADD CONSTRAINT FK_9014574AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE niveau ADD CONSTRAINT FK_4BDFF36BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE notes_etudiant ADD CONSTRAINT FK_1D7861425DAC5993 FOREIGN KEY (inscription_id) REFERENCES inscription (id)');
        $this->addSql('ALTER TABLE notes_etudiant ADD CONSTRAINT FK_1D78614262E883B1 FOREIGN KEY (ue_id) REFERENCES ue (id)');
        $this->addSql('ALTER TABLE notes_etudiant ADD CONSTRAINT FK_1D786142A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE notes_etudiant ADD CONSTRAINT FK_1D7861425577AFDB FOREIGN KEY (semestre_id) REFERENCES semestre (id)');
        $this->addSql('ALTER TABLE semestre ADD CONSTRAINT FK_71688FBCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ue ADD CONSTRAINT FK_2E490A9B180AA129 FOREIGN KEY (filiere_id) REFERENCES filiere (id)');
        $this->addSql('ALTER TABLE ue ADD CONSTRAINT FK_2E490A9BB3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id)');
        $this->addSql('ALTER TABLE ue ADD CONSTRAINT FK_2E490A9BF46CD258 FOREIGN KEY (matiere_id) REFERENCES matiere (id)');
        $this->addSql('ALTER TABLE ue ADD CONSTRAINT FK_2E490A9BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ue ADD CONSTRAINT FK_2E490A9B5577AFDB FOREIGN KEY (semestre_id) REFERENCES semestre (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D6DDEAB1A3');
        $this->addSql('ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D6180AA129');
        $this->addSql('ALTER TABLE ue DROP FOREIGN KEY FK_2E490A9B180AA129');
        $this->addSql('ALTER TABLE notes_etudiant DROP FOREIGN KEY FK_1D7861425DAC5993');
        $this->addSql('ALTER TABLE ue DROP FOREIGN KEY FK_2E490A9BF46CD258');
        $this->addSql('ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D6B3E9C81');
        $this->addSql('ALTER TABLE ue DROP FOREIGN KEY FK_2E490A9BB3E9C81');
        $this->addSql('ALTER TABLE notes_etudiant DROP FOREIGN KEY FK_1D7861425577AFDB');
        $this->addSql('ALTER TABLE ue DROP FOREIGN KEY FK_2E490A9B5577AFDB');
        $this->addSql('ALTER TABLE notes_etudiant DROP FOREIGN KEY FK_1D78614262E883B1');
        $this->addSql('ALTER TABLE etudiant DROP FOREIGN KEY FK_717E22E3A76ED395');
        $this->addSql('ALTER TABLE filiere DROP FOREIGN KEY FK_2ED05D9EA76ED395');
        $this->addSql('ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D6A76ED395');
        $this->addSql('ALTER TABLE matiere DROP FOREIGN KEY FK_9014574AA76ED395');
        $this->addSql('ALTER TABLE niveau DROP FOREIGN KEY FK_4BDFF36BA76ED395');
        $this->addSql('ALTER TABLE notes_etudiant DROP FOREIGN KEY FK_1D786142A76ED395');
        $this->addSql('ALTER TABLE semestre DROP FOREIGN KEY FK_71688FBCA76ED395');
        $this->addSql('ALTER TABLE ue DROP FOREIGN KEY FK_2E490A9BA76ED395');
        $this->addSql('DROP TABLE etudiant');
        $this->addSql('DROP TABLE filiere');
        $this->addSql('DROP TABLE inscription');
        $this->addSql('DROP TABLE matiere');
        $this->addSql('DROP TABLE niveau');
        $this->addSql('DROP TABLE notes_etudiant');
        $this->addSql('DROP TABLE semestre');
        $this->addSql('DROP TABLE ue');
        $this->addSql('DROP TABLE user');
    }
}
