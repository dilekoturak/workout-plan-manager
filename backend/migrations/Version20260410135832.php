<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260410135832 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create initial schema: users, workout_plans, workout_days, exercises, user_workout_plans';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE exercises (id UUID NOT NULL, name VARCHAR(150) NOT NULL, sets INT DEFAULT NULL, reps INT DEFAULT NULL, notes TEXT DEFAULT NULL, workout_day_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_FA1499113415EE8 ON exercises (workout_day_id)');
        $this->addSql('CREATE TABLE user_workout_plans (id UUID NOT NULL, assigned_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, user_id UUID NOT NULL, workout_plan_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_82228695A76ED395 ON user_workout_plans (user_id)');
        $this->addSql('CREATE INDEX IDX_82228695945F6E33 ON user_workout_plans (workout_plan_id)');
        $this->addSql('CREATE UNIQUE INDEX uq_user_workout_plan ON user_workout_plans (user_id, workout_plan_id)');
        $this->addSql('CREATE TABLE users (id UUID NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('CREATE TABLE workout_days (id UUID NOT NULL, name VARCHAR(100) NOT NULL, workout_plan_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_936AFF1A945F6E33 ON workout_days (workout_plan_id)');
        $this->addSql('CREATE TABLE workout_plans (id UUID NOT NULL, name VARCHAR(150) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('ALTER TABLE exercises ADD CONSTRAINT FK_FA1499113415EE8 FOREIGN KEY (workout_day_id) REFERENCES workout_days (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE user_workout_plans ADD CONSTRAINT FK_82228695A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE user_workout_plans ADD CONSTRAINT FK_82228695945F6E33 FOREIGN KEY (workout_plan_id) REFERENCES workout_plans (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE workout_days ADD CONSTRAINT FK_936AFF1A945F6E33 FOREIGN KEY (workout_plan_id) REFERENCES workout_plans (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE exercises DROP CONSTRAINT FK_FA1499113415EE8');
        $this->addSql('ALTER TABLE user_workout_plans DROP CONSTRAINT FK_82228695A76ED395');
        $this->addSql('ALTER TABLE user_workout_plans DROP CONSTRAINT FK_82228695945F6E33');
        $this->addSql('ALTER TABLE workout_days DROP CONSTRAINT FK_936AFF1A945F6E33');
        $this->addSql('DROP TABLE exercises');
        $this->addSql('DROP TABLE user_workout_plans');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE workout_days');
        $this->addSql('DROP TABLE workout_plans');
    }
}
