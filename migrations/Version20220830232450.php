<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220830232450 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE attendee_info (id INT AUTO_INCREMENT NOT NULL, refrenerence VARCHAR(26) NOT NULL, position VARCHAR(255) DEFAULT NULL, years INT NOT NULL, work_preference LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', city VARCHAR(255) DEFAULT NULL, state VARCHAR(255) NOT NULL, stack LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', email VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ticket CHANGE revoked revoked TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE ticket_plan CHANGE few_quantity_alert few_quantity_alert INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE attendee_info');
        $this->addSql('ALTER TABLE ticket CHANGE revoked revoked TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE ticket_plan CHANGE few_quantity_alert few_quantity_alert INT DEFAULT 0 NOT NULL');
    }
}
