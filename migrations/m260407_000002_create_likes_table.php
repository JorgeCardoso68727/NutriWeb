<?php

use yii\db\Migration;

class m260407_000002_create_likes_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%likes}}', [
            'id_like' => $this->primaryKey(),
            'id_post' => $this->integer()->notNull(),
            'id_user' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->createIndex('idx_likes_id_post', '{{%likes}}', 'id_post');
        $this->createIndex('idx_likes_id_user', '{{%likes}}', 'id_user');
        $this->createIndex('ux_likes_id_post_id_user', '{{%likes}}', ['id_post', 'id_user'], true);

        $this->addForeignKey(
            'fk_likes_post',
            '{{%likes}}',
            'id_post',
            '{{%post}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_likes_user',
            '{{%likes}}',
            'id_user',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_likes_user', '{{%likes}}');
        $this->dropForeignKey('fk_likes_post', '{{%likes}}');
        $this->dropTable('{{%likes}}');
    }
}