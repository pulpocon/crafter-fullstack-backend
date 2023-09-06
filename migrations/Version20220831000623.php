<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220831000623 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE lead_attendee_info');
        $this->addSql('ALTER TABLE lead ADD attendee_info_id INT NOT NULL');
        $this->addSql('ALTER TABLE lead ADD CONSTRAINT FK_289161CB5B181300 FOREIGN KEY (attendee_info_id) REFERENCES attendee_info (id)');
        $this->addSql('CREATE INDEX IDX_289161CB5B181300 ON lead (attendee_info_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE lead_attendee_info (lead_id INT NOT NULL, attendee_info_id INT NOT NULL, INDEX IDX_A9B36EF05B181300 (attendee_info_id), INDEX IDX_A9B36EF055458D (lead_id), PRIMARY KEY(lead_id, attendee_info_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE lead_attendee_info ADD CONSTRAINT FK_A9B36EF05B181300 FOREIGN KEY (attendee_info_id) REFERENCES attendee_info (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lead_attendee_info ADD CONSTRAINT FK_A9B36EF055458D FOREIGN KEY (lead_id) REFERENCES lead (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lead DROP FOREIGN KEY FK_289161CB5B181300');
        $this->addSql('DROP INDEX IDX_289161CB5B181300 ON lead');
        $this->addSql('ALTER TABLE lead DROP attendee_info_id');
    }
}
