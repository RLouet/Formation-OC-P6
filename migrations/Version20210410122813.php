<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210410122813 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trick ADD hero_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE trick ADD CONSTRAINT FK_D8F0A91E45B0BCD FOREIGN KEY (hero_id) REFERENCES image (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D8F0A91E45B0BCD ON trick (hero_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trick DROP FOREIGN KEY FK_D8F0A91E45B0BCD');
        $this->addSql('DROP INDEX UNIQ_D8F0A91E45B0BCD ON trick');
        $this->addSql('ALTER TABLE trick DROP hero_id');
    }
}
