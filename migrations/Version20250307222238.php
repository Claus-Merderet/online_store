<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250307222238 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cart (id SERIAL NOT NULL, user_id INT NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BA388B7A76ED395 ON cart (user_id)');
        $this->addSql('CREATE TABLE "cart_items" (id SERIAL NOT NULL, cart_id INT NOT NULL, product_id INT NOT NULL, quantity INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BEF484451AD5CDBF ON "cart_items" (cart_id)');
        $this->addSql('CREATE INDEX IDX_BEF484454584665A ON "cart_items" (product_id)');
        $this->addSql('CREATE TABLE "order_products" (id SERIAL NOT NULL, order_id INT NOT NULL, product_id INT NOT NULL, product_name VARCHAR(255) NOT NULL, price INT NOT NULL, amount INT NOT NULL, height INT NOT NULL, weight INT NOT NULL, length INT NOT NULL, width INT NOT NULL, tax INT NOT NULL, version INT NOT NULL, description VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5242B8EB8D9F6D38 ON "order_products" (order_id)');
        $this->addSql('CREATE INDEX IDX_5242B8EB4584665A ON "order_products" (product_id)');
        $this->addSql('CREATE TABLE "order_status_history" (id SERIAL NOT NULL, order_id INT NOT NULL, created_by_id INT NOT NULL, status_name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, comment VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_471AD77E8D9F6D38 ON "order_status_history" (order_id)');
        $this->addSql('CREATE INDEX IDX_471AD77EB03A8386 ON "order_status_history" (created_by_id)');
        $this->addSql('CREATE TABLE "orders" (id SERIAL NOT NULL, user_id INT NOT NULL, notification_type VARCHAR(255) NOT NULL, address VARCHAR(255) DEFAULT NULL, kladr_id INT DEFAULT NULL, user_phone VARCHAR(15) DEFAULT NULL, user_email VARCHAR(255) DEFAULT NULL, delivery_type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E52FFDEEA76ED395 ON "orders" (user_id)');
        $this->addSql('CREATE TABLE "products" (id INT NOT NULL, name VARCHAR(255) NOT NULL, weight INT NOT NULL, height INT NOT NULL, width INT NOT NULL, length INT NOT NULL, description VARCHAR(255) DEFAULT NULL, price INT NOT NULL, tax INT NOT NULL, version INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE refresh_tokens (id SERIAL NOT NULL, refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9BACE7E1C74F2195 ON refresh_tokens (refresh_token)');
        $this->addSql('CREATE TABLE report (id UUID NOT NULL, status VARCHAR(20) NOT NULL, file_path VARCHAR(255) DEFAULT NULL, report_type VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN report.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE "roles" (id SERIAL NOT NULL, role_name VARCHAR(100) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B63E2EC7E09C0C92 ON "roles" (role_name)');
        $this->addSql('CREATE TABLE "user_addresses" (id SERIAL NOT NULL, user_id INT NOT NULL, kladr_id INT DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6F2AF8F2A76ED395 ON "user_addresses" (user_id)');
        $this->addSql('CREATE TABLE "users" (id SERIAL NOT NULL, role_id INT NOT NULL, promo_id VARCHAR(36) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, birthday DATE DEFAULT NULL, phone VARCHAR(15) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, password VARCHAR(64) NOT NULL, deleted_at DATE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON "users" (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9444F97DD ON "users" (phone)');
        $this->addSql('CREATE INDEX IDX_1483A5E9D60322AC ON "users" (role_id)');
        $this->addSql('ALTER TABLE cart ADD CONSTRAINT FK_BA388B7A76ED395 FOREIGN KEY (user_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "cart_items" ADD CONSTRAINT FK_BEF484451AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "cart_items" ADD CONSTRAINT FK_BEF484454584665A FOREIGN KEY (product_id) REFERENCES "products" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "order_products" ADD CONSTRAINT FK_5242B8EB8D9F6D38 FOREIGN KEY (order_id) REFERENCES "orders" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "order_products" ADD CONSTRAINT FK_5242B8EB4584665A FOREIGN KEY (product_id) REFERENCES "products" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "order_status_history" ADD CONSTRAINT FK_471AD77E8D9F6D38 FOREIGN KEY (order_id) REFERENCES "orders" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "order_status_history" ADD CONSTRAINT FK_471AD77EB03A8386 FOREIGN KEY (created_by_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "orders" ADD CONSTRAINT FK_E52FFDEEA76ED395 FOREIGN KEY (user_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user_addresses" ADD CONSTRAINT FK_6F2AF8F2A76ED395 FOREIGN KEY (user_id) REFERENCES "users" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "users" ADD CONSTRAINT FK_1483A5E9D60322AC FOREIGN KEY (role_id) REFERENCES "roles" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE cart DROP CONSTRAINT FK_BA388B7A76ED395');
        $this->addSql('ALTER TABLE "cart_items" DROP CONSTRAINT FK_BEF484451AD5CDBF');
        $this->addSql('ALTER TABLE "cart_items" DROP CONSTRAINT FK_BEF484454584665A');
        $this->addSql('ALTER TABLE "order_products" DROP CONSTRAINT FK_5242B8EB8D9F6D38');
        $this->addSql('ALTER TABLE "order_products" DROP CONSTRAINT FK_5242B8EB4584665A');
        $this->addSql('ALTER TABLE "order_status_history" DROP CONSTRAINT FK_471AD77E8D9F6D38');
        $this->addSql('ALTER TABLE "order_status_history" DROP CONSTRAINT FK_471AD77EB03A8386');
        $this->addSql('ALTER TABLE "orders" DROP CONSTRAINT FK_E52FFDEEA76ED395');
        $this->addSql('ALTER TABLE "user_addresses" DROP CONSTRAINT FK_6F2AF8F2A76ED395');
        $this->addSql('ALTER TABLE "users" DROP CONSTRAINT FK_1483A5E9D60322AC');
        $this->addSql('DROP TABLE cart');
        $this->addSql('DROP TABLE "cart_items"');
        $this->addSql('DROP TABLE "order_products"');
        $this->addSql('DROP TABLE "order_status_history"');
        $this->addSql('DROP TABLE "orders"');
        $this->addSql('DROP TABLE "products"');
        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('DROP TABLE report');
        $this->addSql('DROP TABLE "roles"');
        $this->addSql('DROP TABLE "user_addresses"');
        $this->addSql('DROP TABLE "users"');
    }
}
