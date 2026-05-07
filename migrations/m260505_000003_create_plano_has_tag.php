<?php

use yii\db\Migration;

/**
 * Creates the plano_has_tag junction table for associating recipe tags with nutritional plans.
 */
class m260505_000003_create_plano_has_tag extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Create plano_has_tag junction table
        $this->createTable('plano_has_tag', [
            'plano_id' => $this->integer()->notNull(),
            'tag_id' => $this->integer()->notNull(),
        ]);

        $this->addPrimaryKey('pk_plano_has_tag', 'plano_has_tag', ['plano_id', 'tag_id']);

        $this->addForeignKey(
            'fk_plano_has_tag_plano',
            'plano_has_tag',
            'plano_id',
            'plano_nutricional',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_plano_has_tag_tag',
            'plano_has_tag',
            'tag_id',
            'recipe_tag',
            'id',
            'CASCADE'
        );

        $this->createIndex('idx_plano_has_tag_plano', 'plano_has_tag', 'plano_id');
        $this->createIndex('idx_plano_has_tag_tag', 'plano_has_tag', 'tag_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_plano_has_tag_tag', 'plano_has_tag');
        $this->dropForeignKey('fk_plano_has_tag_plano', 'plano_has_tag');
        $this->dropTable('plano_has_tag');
    }
}
