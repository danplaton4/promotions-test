<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230406175033 extends AbstractMigration {
  public function getDescription(): string {
    return '';
  }

  public function up(Schema $schema): void {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('CREATE TABLE media (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
    $this->addSql('CREATE TABLE partners (id INT AUTO_INCREMENT NOT NULL, url VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_EFEB516477153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
    $this->addSql('CREATE TABLE partners_translations (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_37856C9A2C2AC5D3 (translatable_id), UNIQUE INDEX partners_translations_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
    $this->addSql('CREATE TABLE prizes (id INT AUTO_INCREMENT NOT NULL, promotion_id INT NOT NULL, code VARCHAR(255) NOT NULL, partner_code VARCHAR(255) NOT NULL, start_date DATETIME NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_F73CF5A677153098 (code), INDEX IDX_F73CF5A6139DF194 (promotion_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
    $this->addSql('CREATE TABLE prizes_translations (id INT AUTO_INCREMENT NOT NULL, translatable_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, locale VARCHAR(5) NOT NULL, INDEX IDX_23D3DE2E2C2AC5D3 (translatable_id), UNIQUE INDEX prizes_translations_unique_translation (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
    $this->addSql('CREATE TABLE promotions (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, start_date DATETIME NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
    $this->addSql('CREATE TABLE refresh_tokens (id INT AUTO_INCREMENT NOT NULL, refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid DATETIME NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_9BACE7E1C74F2195 (refresh_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
    $this->addSql('CREATE TABLE user_profiles (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, avatar VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_6BBD6130A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
    $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, enabled TINYINT(1) NOT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
    $this->addSql('CREATE TABLE winnings (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, prize_id INT NOT NULL, promotion_id INT NOT NULL, winning_date DATETIME NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_2EB26A95A76ED395 (user_id), INDEX IDX_2EB26A95BBE43214 (prize_id), INDEX IDX_2EB26A95139DF194 (promotion_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
    $this->addSql('ALTER TABLE partners_translations ADD CONSTRAINT FK_37856C9A2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES partners (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE prizes ADD CONSTRAINT FK_F73CF5A6139DF194 FOREIGN KEY (promotion_id) REFERENCES promotions (id)');
    $this->addSql('ALTER TABLE prizes_translations ADD CONSTRAINT FK_23D3DE2E2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES prizes (id) ON DELETE CASCADE');
    $this->addSql('ALTER TABLE user_profiles ADD CONSTRAINT FK_6BBD6130A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
    $this->addSql('ALTER TABLE winnings ADD CONSTRAINT FK_2EB26A95A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
    $this->addSql('ALTER TABLE winnings ADD CONSTRAINT FK_2EB26A95BBE43214 FOREIGN KEY (prize_id) REFERENCES prizes (id)');
    $this->addSql('ALTER TABLE winnings ADD CONSTRAINT FK_2EB26A95139DF194 FOREIGN KEY (promotion_id) REFERENCES promotions (id)');
  }

  public function down(Schema $schema): void {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('ALTER TABLE partners_translations DROP FOREIGN KEY FK_37856C9A2C2AC5D3');
    $this->addSql('ALTER TABLE prizes DROP FOREIGN KEY FK_F73CF5A6139DF194');
    $this->addSql('ALTER TABLE prizes_translations DROP FOREIGN KEY FK_23D3DE2E2C2AC5D3');
    $this->addSql('ALTER TABLE user_profiles DROP FOREIGN KEY FK_6BBD6130A76ED395');
    $this->addSql('ALTER TABLE winnings DROP FOREIGN KEY FK_2EB26A95A76ED395');
    $this->addSql('ALTER TABLE winnings DROP FOREIGN KEY FK_2EB26A95BBE43214');
    $this->addSql('ALTER TABLE winnings DROP FOREIGN KEY FK_2EB26A95139DF194');
    $this->addSql('DROP TABLE media');
    $this->addSql('DROP TABLE partners');
    $this->addSql('DROP TABLE partners_translations');
    $this->addSql('DROP TABLE prizes');
    $this->addSql('DROP TABLE prizes_translations');
    $this->addSql('DROP TABLE promotions');
    $this->addSql('DROP TABLE refresh_tokens');
    $this->addSql('DROP TABLE user_profiles');
    $this->addSql('DROP TABLE users');
    $this->addSql('DROP TABLE winnings');
  }
}
