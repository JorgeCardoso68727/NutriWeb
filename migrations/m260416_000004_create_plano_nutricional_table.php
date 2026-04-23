<?php

use yii\db\Migration;

class m260416_000004_create_plano_nutricional_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%plano_nutricional}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'titulo' => $this->string(255)->notNull(),
            'objetivo' => $this->string(255)->null(),
            'descricao' => $this->text()->notNull(),
            'estrutura_json' => $this->text()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->createIndex('idx_plano_nutricional_user_id', '{{%plano_nutricional}}', 'user_id');
        $this->createIndex('idx_plano_nutricional_created_at', '{{%plano_nutricional}}', 'created_at');

        $this->addForeignKey(
            'fk_plano_nutricional_user',
            '{{%plano_nutricional}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_plano_nutricional_user', '{{%plano_nutricional}}');
        $this->dropTable('{{%plano_nutricional}}');
    }
}
