<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220603081222 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ticket CHANGE reference reference VARCHAR(26) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_97A0ADA3AEA34913 ON ticket (reference)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_97A0ADA3AEA34913 ON ticket');
        $this->addSql('ALTER TABLE ticket CHANGE reference reference BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');
    }
}
