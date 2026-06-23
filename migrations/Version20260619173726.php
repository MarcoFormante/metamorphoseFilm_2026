<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260619173726 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE gallery_images (id INT AUTO_INCREMENT NOT NULL, position INT NOT NULL, name_id INT NOT NULL, INDEX IDX_429C52C871179CD6 (name_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE gallery_images ADD CONSTRAINT FK_429C52C871179CD6 FOREIGN KEY (name_id) REFERENCES gallery (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gallery_images DROP FOREIGN KEY FK_429C52C871179CD6');
        $this->addSql('DROP TABLE gallery_images');
    }
}
