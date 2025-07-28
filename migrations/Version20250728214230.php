<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250728214230 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE account (id INT AUTO_INCREMENT NOT NULL, online_id VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, balance NUMERIC(10, 0) NOT NULL, description LONGTEXT DEFAULT NULL, synced TINYINT(1) NOT NULL, create_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', contact VARCHAR(255) DEFAULT NULL, emplacement VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, account_local_id INT DEFAULT NULL, accountonline_id INT DEFAULT NULL, type_transaction_id INT DEFAULT NULL, online_id VARCHAR(255) DEFAULT NULL, amount NUMERIC(10, 0) NOT NULL, description LONGTEXT NOT NULL, person_name VARCHAR(255) NOT NULL, transaction_date DATE NOT NULL, synced TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_723705D12594C3B7 (account_local_id), INDEX IDX_723705D126ABE5B8 (accountonline_id), INDEX IDX_723705D17903E29B (type_transaction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type_transaction (id INT AUTO_INCREMENT NOT NULL, online_id VARCHAR(255) DEFAULT NULL, libelle VARCHAR(255) NOT NULL, color VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D12594C3B7 FOREIGN KEY (account_local_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D126ABE5B8 FOREIGN KEY (accountonline_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D17903E29B FOREIGN KEY (type_transaction_id) REFERENCES type_transaction (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D12594C3B7');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D126ABE5B8');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D17903E29B');
        $this->addSql('DROP TABLE account');
        $this->addSql('DROP TABLE transaction');
        $this->addSql('DROP TABLE type_transaction');
    }
}
