<?php

use yii\db\Migration;

class m260402_120001_create_pedido_badge_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%badge_pedido}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'diploma_pdf' => $this->string(255)->notNull(),
            'estado' => $this->string(20)->notNull()->defaultValue('pendente'),
            'admin_user_id' => $this->integer()->null(),
            'observacao' => $this->text()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_badge_pedido_user_id', '{{%badge_pedido}}', 'user_id');
        $this->createIndex('idx_badge_pedido_estado', '{{%badge_pedido}}', 'estado');

        $this->addForeignKey(
            'fk_badge_pedido_user',
            '{{%badge_pedido}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_badge_pedido_admin_user',
            '{{%badge_pedido}}',
            'admin_user_id',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_badge_pedido_admin_user', '{{%badge_pedido}}');
        $this->dropForeignKey('fk_badge_pedido_user', '{{%badge_pedido}}');
        $this->dropTable('{{%badge_pedido}}');
    }
}
