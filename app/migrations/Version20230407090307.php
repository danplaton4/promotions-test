<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230407090307 extends AbstractMigration {
  public function getDescription(): string {
    return '';
  }

  public function up(Schema $schema): void {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('ALTER TABLE prizes DROP FOREIGN KEY FK_F73CF5A627210380');
    $this->addSql('DROP INDEX IDX_F73CF5A627210380 ON prizes');
    $this->addSql('ALTER TABLE prizes ADD is_won TINYINT(1) NOT NULL');
  }

  public function down(Schema $schema): void {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('ALTER TABLE prizes DROP is_won');
    $this->addSql('ALTER TABLE prizes ADD CONSTRAINT FK_F73CF5A627210380 FOREIGN KEY (partner_code) REFERENCES partners (code) ON UPDATE NO ACTION ON DELETE NO ACTION');
    $this->addSql('CREATE INDEX IDX_F73CF5A627210380 ON prizes (partner_code)');
  }
}
