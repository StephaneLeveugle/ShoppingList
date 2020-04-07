<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200404094113 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE shopping_list_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE shopping_list (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE shopping_list_user (shopping_list_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(shopping_list_id, user_id))');
        $this->addSql('CREATE INDEX IDX_DD39317323245BF9 ON shopping_list_user (shopping_list_id)');
        $this->addSql('CREATE INDEX IDX_DD393173A76ED395 ON shopping_list_user (user_id)');
        $this->addSql('ALTER TABLE shopping_list_user ADD CONSTRAINT FK_DD39317323245BF9 FOREIGN KEY (shopping_list_id) REFERENCES shopping_list (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE shopping_list_user ADD CONSTRAINT FK_DD393173A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE item ADD list_id INT NOT NULL');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E3DAE168B FOREIGN KEY (list_id) REFERENCES shopping_list (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1F1B251E3DAE168B ON item (list_id)');
        $this->addSql('ALTER INDEX uniq_327c5de7e7927c74 RENAME TO UNIQ_8D93D649E7927C74');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE item DROP CONSTRAINT FK_1F1B251E3DAE168B');
        $this->addSql('ALTER TABLE shopping_list_user DROP CONSTRAINT FK_DD39317323245BF9');
        $this->addSql('DROP SEQUENCE shopping_list_id_seq CASCADE');
        $this->addSql('DROP TABLE shopping_list');
        $this->addSql('DROP TABLE shopping_list_user');
        $this->addSql('DROP INDEX IDX_1F1B251E3DAE168B');
        $this->addSql('ALTER TABLE item DROP list_id');
        $this->addSql('ALTER INDEX uniq_8d93d649e7927c74 RENAME TO uniq_327c5de7e7927c74');
    }
}
