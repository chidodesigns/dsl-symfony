<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221025054901 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE attribute (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fact (security_id INT NOT NULL, attribute_id INT NOT NULL, value DOUBLE PRECISION NOT NULL, INDEX IDX_6FA45B956DBE4214 (security_id), INDEX IDX_6FA45B95B6E62EFA (attribute_id), PRIMARY KEY(security_id, attribute_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE security (id INT AUTO_INCREMENT NOT NULL, symbol VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fact ADD CONSTRAINT FK_6FA45B956DBE4214 FOREIGN KEY (security_id) REFERENCES security (id)');
        $this->addSql('ALTER TABLE fact ADD CONSTRAINT FK_6FA45B95B6E62EFA FOREIGN KEY (attribute_id) REFERENCES attribute (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fact DROP FOREIGN KEY FK_6FA45B956DBE4214');
        $this->addSql('ALTER TABLE fact DROP FOREIGN KEY FK_6FA45B95B6E62EFA');
        $this->addSql('DROP TABLE attribute');
        $this->addSql('DROP TABLE fact');
        $this->addSql('DROP TABLE security');
    }
}
