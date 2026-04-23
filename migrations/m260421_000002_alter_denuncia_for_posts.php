<?php

use yii\db\Migration;

class m260421_000002_alter_denuncia_for_posts extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%denuncia}}', 'target_type', $this->string(20)->notNull()->defaultValue('profile'));
        $this->addColumn('{{%denuncia}}', 'target_post_id', $this->integer()->null());

        $this->createIndex('idx_denuncia_target_type', '{{%denuncia}}', 'target_type');
        $this->createIndex('idx_denuncia_target_post_id', '{{%denuncia}}', 'target_post_id');
        $this->createIndex('idx_denuncia_target_type_target_user_id', '{{%denuncia}}', ['target_type', 'target_user_id']);

        $this->addForeignKey(
            'fk_denuncia_target_post',
            '{{%denuncia}}',
            'target_post_id',
            '{{%post}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_denuncia_target_post', '{{%denuncia}}');
        $this->dropIndex('idx_denuncia_target_type_target_user_id', '{{%denuncia}}');
        $this->dropIndex('idx_denuncia_target_post_id', '{{%denuncia}}');
        $this->dropIndex('idx_denuncia_target_type', '{{%denuncia}}');
        $this->dropColumn('{{%denuncia}}', 'target_post_id');
        $this->dropColumn('{{%denuncia}}', 'target_type');
    }
}
