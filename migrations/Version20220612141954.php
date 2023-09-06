<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220612141954 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ticket ADD upgraded_from_id INT DEFAULT NULL, DROP upgraded_from');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3707F2E8E FOREIGN KEY (upgraded_from_id) REFERENCES ticket (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_97A0ADA3707F2E8E ON ticket (upgraded_from_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3707F2E8E');
        $this->addSql('DROP INDEX UNIQ_97A0ADA3707F2E8E ON ticket');
        $this->addSql('ALTER TABLE ticket ADD upgraded_from VARCHAR(26) DEFAULT NULL, DROP upgraded_from_id');
    }
}
