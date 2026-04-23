<?php

use yii\db\Migration;

class m260421_000003_alter_denuncia_autor_nullable extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%denuncia}}', 'autor_id', $this->integer()->null());
    }

    public function safeDown()
    {
        $this->alterColumn('{{%denuncia}}', 'autor_id', $this->integer()->notNull());
    }
}
