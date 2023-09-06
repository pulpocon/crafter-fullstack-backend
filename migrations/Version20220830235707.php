<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220830235707 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE lead (id INT AUTO_INCREMENT NOT NULL, sponsor VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lead_attendee_info (lead_id INT NOT NULL, attendee_info_id INT NOT NULL, INDEX IDX_A9B36EF055458D (lead_id), INDEX IDX_A9B36EF05B181300 (attendee_info_id), PRIMARY KEY(lead_id, attendee_info_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE lead_attendee_info ADD CONSTRAINT FK_A9B36EF055458D FOREIGN KEY (lead_id) REFERENCES lead (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lead_attendee_info ADD CONSTRAINT FK_A9B36EF05B181300 FOREIGN KEY (attendee_info_id) REFERENCES attendee_info (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE attendee_info CHANGE refrenerence reference VARCHAR(26) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lead_attendee_info DROP FOREIGN KEY FK_A9B36EF055458D');
        $this->addSql('DROP TABLE lead');
        $this->addSql('DROP TABLE lead_attendee_info');
        $this->addSql('ALTER TABLE attendee_info CHANGE reference refrenerence VARCHAR(26) NOT NULL');
    }
}
