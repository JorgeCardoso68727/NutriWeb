<?php

use yii\db\Migration;

/**
 * Handles the creation of table `event`, `event_category`, `event_participant`, 
 * `recipe_tag`, and recipe-related tables for the nutriweb application.
 */
class m260505_000001_create_events_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Create event_category table
        $this->createTable('event_category', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull(),
            'description' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // Create event table
        $this->createTable('event', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'description' => $this->text()->notNull(),
            'category_id' => $this->integer()->notNull(),
            'creator_id' => $this->integer()->notNull(),
            'location' => $this->string(255),
            'start_date' => $this->dateTime()->notNull(),
            'end_date' => $this->dateTime(),
            'image' => $this->string(255),
            'max_participants' => $this->integer(),
            'status' => $this->string(20)->defaultValue('active'), // active, cancelled, completed
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Create foreign key for event table
        $this->addForeignKey(
            'fk_event_category',
            'event',
            'category_id',
            'event_category',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_event_creator',
            'event',
            'creator_id',
            'user',
            'id',
            'CASCADE'
        );

        // Create event_participant table
        $this->createTable('event_participant', [
            'event_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'status' => $this->string(20)->defaultValue('registered'), // registered, attended, cancelled
            'registered_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->addPrimaryKey('pk_event_participant', 'event_participant', ['event_id', 'user_id']);

        $this->addForeignKey(
            'fk_event_participant_event',
            'event_participant',
            'event_id',
            'event',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_event_participant_user',
            'event_participant',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );

        // Create recipe table (if not exists)
        if (!$this->tableExists('recipe')) {
            $this->createTable('recipe', [
                'id' => $this->primaryKey(),
                'title' => $this->string(255)->notNull(),
                'description' => $this->text(),
                'instructions' => $this->text()->notNull(),
                'creator_id' => $this->integer()->notNull(),
                'prep_time' => $this->integer(), // in minutes
                'cook_time' => $this->integer(), // in minutes
                'difficulty' => $this->string(20), // easy, medium, hard
                'servings' => $this->integer(),
                'image' => $this->string(255),
                'status' => $this->string(20)->defaultValue('draft'), // draft, published
                'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
                'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            ]);

            $this->addForeignKey(
                'fk_recipe_creator',
                'recipe',
                'creator_id',
                'user',
                'id',
                'CASCADE'
            );
        }

        // Create recipe_tag table
        $this->createTable('recipe_tag', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull()->unique(),
            'description' => $this->string(255),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // Create recipe_has_tag junction table
        $this->createTable('recipe_has_tag', [
            'recipe_id' => $this->integer()->notNull(),
            'tag_id' => $this->integer()->notNull(),
        ]);

        $this->addPrimaryKey('pk_recipe_has_tag', 'recipe_has_tag', ['recipe_id', 'tag_id']);

        $this->addForeignKey(
            'fk_recipe_has_tag_recipe',
            'recipe_has_tag',
            'recipe_id',
            'recipe',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_recipe_has_tag_tag',
            'recipe_has_tag',
            'tag_id',
            'recipe_tag',
            'id',
            'CASCADE'
        );

        // Create indexes for better search performance
        $this->createIndex('idx_event_category', 'event', 'category_id');
        $this->createIndex('idx_event_creator', 'event', 'creator_id');
        $this->createIndex('idx_event_start_date', 'event', 'start_date');
        $this->createIndex('idx_event_status', 'event', 'status');
        $this->createIndex('idx_recipe_creator', 'recipe', 'creator_id');
        $this->createIndex('idx_recipe_difficulty', 'recipe', 'difficulty');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_recipe_has_tag_tag', 'recipe_has_tag');
        $this->dropForeignKey('fk_recipe_has_tag_recipe', 'recipe_has_tag');
        $this->dropTable('recipe_has_tag');
        $this->dropTable('recipe_tag');

        $this->dropForeignKey('fk_recipe_creator', 'recipe');
        $this->dropTable('recipe');

        $this->dropForeignKey('fk_event_participant_user', 'event_participant');
        $this->dropForeignKey('fk_event_participant_event', 'event_participant');
        $this->dropTable('event_participant');

        $this->dropForeignKey('fk_event_creator', 'event');
        $this->dropForeignKey('fk_event_category', 'event');
        $this->dropTable('event');

        $this->dropTable('event_category');
    }

    /**
     * Check if table exists
     */
    protected function tableExists($tableName)
    {
        return in_array($tableName, $this->db->getSchema()->getTableNames());
    }
}
