<?php

use yii\db\Migration;

class m260422_000005_add_review_status_to_denuncia extends Migration
{
    public function safeUp()
    {
        $table = '{{%denuncia}}';
        $schema = $this->db->schema->getTableSchema($this->db->getSchema()->getRawTableName($table), true);

        if ($schema === null) {
            return;
        }

        if (!isset($schema->columns['estado_revisao'])) {
            $this->addColumn($table, 'estado_revisao', $this->string(20)->notNull()->defaultValue('pendente'));
        }

        $schema = $this->db->schema->getTableSchema($this->db->getSchema()->getRawTableName($table), true);
        if ($schema !== null && isset($schema->columns['estado_revisao'])) {
            $this->update($table, ['estado_revisao' => 'pendente'], ['estado_revisao' => null]);
            $this->createIndex('idx_denuncia_estado_revisao', $table, 'estado_revisao');
            $this->createIndex('idx_denuncia_target_type_estado_revisao', $table, ['target_type', 'estado_revisao']);
        }
    }

    public function safeDown()
    {
        $table = '{{%denuncia}}';
        $schema = $this->db->schema->getTableSchema($this->db->getSchema()->getRawTableName($table), true);

        if ($schema === null || !isset($schema->columns['estado_revisao'])) {
            return;
        }

        $this->dropIndex('idx_denuncia_target_type_estado_revisao', $table);
        $this->dropIndex('idx_denuncia_estado_revisao', $table);
        $this->dropColumn($table, 'estado_revisao');
    }
}
