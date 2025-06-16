<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250615090723 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__product_variant AS SELECT id, parent_id, name, sku, price FROM product_variant
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE product_variant
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE product_variant (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, parent_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, sku VARCHAR(255) NOT NULL, price NUMERIC(10, 2) DEFAULT NULL, CONSTRAINT FK_209AA41D727ACA70 FOREIGN KEY (parent_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO product_variant (id, parent_id, name, sku, price) SELECT id, parent_id, name, sku, price FROM __temp__product_variant
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__product_variant
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_209AA41D727ACA70 ON product_variant (parent_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_209AA41DF9038C4 ON product_variant (sku)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE __temp__product_variant AS SELECT id, parent_id, name, sku, price FROM product_variant
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE product_variant
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE product_variant (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, parent_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, sku VARCHAR(255) NOT NULL, price DOUBLE PRECISION DEFAULT NULL, CONSTRAINT FK_209AA41D727ACA70 FOREIGN KEY (parent_id) REFERENCES product (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO product_variant (id, parent_id, name, sku, price) SELECT id, parent_id, name, sku, price FROM __temp__product_variant
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE __temp__product_variant
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_209AA41D727ACA70 ON product_variant (parent_id)
        SQL);
    }
}
