<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260219093404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE menu_dish (menu_id INT NOT NULL, dish_id INT NOT NULL, INDEX IDX_5D327CF6CCD7E912 (menu_id), INDEX IDX_5D327CF6148EB0CB (dish_id), PRIMARY KEY (menu_id, dish_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE menu_dish ADD CONSTRAINT FK_5D327CF6CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE menu_dish ADD CONSTRAINT FK_5D327CF6148EB0CB FOREIGN KEY (dish_id) REFERENCES dish (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE menu_image ADD CONSTRAINT FK_54912738CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993989395C3F3 FOREIGN KEY (customer_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6CFFE9AD6 FOREIGN KEY (orders_id) REFERENCES `order` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu_dish DROP FOREIGN KEY FK_5D327CF6CCD7E912');
        $this->addSql('ALTER TABLE menu_dish DROP FOREIGN KEY FK_5D327CF6148EB0CB');
        $this->addSql('DROP TABLE menu_dish');
        $this->addSql('ALTER TABLE menu_image DROP FOREIGN KEY FK_54912738CCD7E912');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993989395C3F3');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398CCD7E912');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6CFFE9AD6');
    }
}
