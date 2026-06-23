<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260619215109 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gallery_images DROP FOREIGN KEY `FK_429C52C8CD3C568A`');
        $this->addSql('DROP INDEX IDX_429C52C8CD3C568A ON gallery_images');
        $this->addSql('ALTER TABLE gallery_images ADD gallery_id INT NOT NULL, DROP gallery_name');
        $this->addSql('ALTER TABLE gallery_images ADD CONSTRAINT FK_429C52C84E7AF8F FOREIGN KEY (gallery_id) REFERENCES gallery (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_429C52C84E7AF8F ON gallery_images (gallery_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gallery_images DROP FOREIGN KEY FK_429C52C84E7AF8F');
        $this->addSql('DROP INDEX IDX_429C52C84E7AF8F ON gallery_images');
        $this->addSql('ALTER TABLE gallery_images ADD gallery_name VARCHAR(255) NOT NULL, DROP gallery_id');
        $this->addSql('ALTER TABLE gallery_images ADD CONSTRAINT `FK_429C52C8CD3C568A` FOREIGN KEY (gallery_name) REFERENCES gallery (name) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_429C52C8CD3C568A ON gallery_images (gallery_name)');
    }
}
