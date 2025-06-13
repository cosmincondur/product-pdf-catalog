<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250612152450 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE attribute_name_attribute_value (attribute_name_id INTEGER NOT NULL, attribute_value_id INTEGER NOT NULL, PRIMARY KEY(attribute_name_id, attribute_value_id), CONSTRAINT FK_C827BE43FA8BC512 FOREIGN KEY (attribute_name_id) REFERENCES attribute_name (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_C827BE4365A22152 FOREIGN KEY (attribute_value_id) REFERENCES attribute_value (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C827BE43FA8BC512 ON attribute_name_attribute_value (attribute_name_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C827BE4365A22152 ON attribute_name_attribute_value (attribute_value_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE product_variant_attribute_value (product_variant_id INTEGER NOT NULL, attribute_value_id INTEGER NOT NULL, PRIMARY KEY(product_variant_id, attribute_value_id), CONSTRAINT FK_A44FC90FA80EF684 FOREIGN KEY (product_variant_id) REFERENCES product_variant (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A44FC90F65A22152 FOREIGN KEY (attribute_value_id) REFERENCES attribute_value (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A44FC90FA80EF684 ON product_variant_attribute_value (product_variant_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A44FC90F65A22152 ON product_variant_attribute_value (attribute_value_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__product AS SELECT id, name FROM product
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE product
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE product (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, category_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO product (id, name) SELECT id, name FROM __temp__product
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__product
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D34A04AD12469DE2 ON product (category_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__product_variant AS SELECT id, name, sku, price FROM product_variant
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE product_variant
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE product_variant (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, parent_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, sku VARCHAR(255) NOT NULL, price DOUBLE PRECISION DEFAULT NULL, CONSTRAINT FK_209AA41D727ACA70 FOREIGN KEY (parent_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO product_variant (id, name, sku, price) SELECT id, name, sku, price FROM __temp__product_variant
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__product_variant
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_209AA41D727ACA70 ON product_variant (parent_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE attribute_name_attribute_value
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE product_variant_attribute_value
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__product AS SELECT id, name FROM product
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE product
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE product (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO product (id, name) SELECT id, name FROM __temp__product
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__product
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__product_variant AS SELECT id, name, sku, price FROM product_variant
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE product_variant
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE product_variant (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, sku VARCHAR(255) NOT NULL, price DOUBLE PRECISION DEFAULT NULL)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO product_variant (id, name, sku, price) SELECT id, name, sku, price FROM __temp__product_variant
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__product_variant
        SQL);
    }
}
