<?php

use yii\db\Migration;

class m260423_000001_add_meta_diaria_agua_to_perfil_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%perfil}}', 'meta_diaria_agua', $this->integer()->notNull()->defaultValue(2000)->after('Telefone'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%perfil}}', 'meta_diaria_agua');
    }
}