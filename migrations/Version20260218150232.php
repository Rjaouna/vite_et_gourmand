<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260218150232 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu_image ADD menu_id INT NOT NULL');
        $this->addSql('ALTER TABLE menu_image ADD CONSTRAINT FK_54912738CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_54912738CCD7E912 ON menu_image (menu_id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993989395C3F3 FOREIGN KEY (customer_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6CFFE9AD6 FOREIGN KEY (orders_id) REFERENCES `order` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu_image DROP FOREIGN KEY FK_54912738CCD7E912');
        $this->addSql('DROP INDEX IDX_54912738CCD7E912 ON menu_image');
        $this->addSql('ALTER TABLE menu_image DROP menu_id');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993989395C3F3');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398CCD7E912');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6CFFE9AD6');
    }
}
