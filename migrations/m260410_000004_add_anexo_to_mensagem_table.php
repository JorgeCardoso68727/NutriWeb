<?php

use yii\db\Migration;

class m260410_000004_add_anexo_to_mensagem_table extends Migration
{
    public function safeUp()
    {
        if ($this->db->schema->getTableSchema('{{%mensagem}}', true) === null) {
            return;
        }

        $tableSchema = $this->db->schema->getTableSchema('{{%mensagem}}', true);
        if ($tableSchema !== null && !isset($tableSchema->columns['anexo'])) {
            $this->addColumn('{{%mensagem}}', 'anexo', $this->string(255)->null()->after('conteudo'));
        }
    }

    public function safeDown()
    {
        $tableSchema = $this->db->schema->getTableSchema('{{%mensagem}}', true);
        if ($tableSchema === null || !isset($tableSchema->columns['anexo'])) {
            return;
        }

        $this->dropColumn('{{%mensagem}}', 'anexo');
    }
}