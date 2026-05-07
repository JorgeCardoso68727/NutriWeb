<?php

use yii\db\Migration;

class m260507_000006_add_target_event_id_to_denuncia extends Migration
{
    public function safeUp()
    {
        $table = '{{%denuncia}}';
        $rawTable = $this->db->getSchema()->getRawTableName($table);
        $schema = $this->db->schema->getTableSchema($rawTable, true);

        if ($schema === null) {
            return;
        }

        if (!isset($schema->columns['target_event_id'])) {
            $this->addColumn($table, 'target_event_id', $this->integer()->null());
        }

        $schema = $this->db->schema->getTableSchema($rawTable, true);
        if ($schema === null || !isset($schema->columns['target_event_id'])) {
            return;
        }

        $foreignKeys = $schema->foreignKeys;
        if (!array_key_exists('fk_denuncia_target_event', $foreignKeys)) {
            $this->addForeignKey(
                'fk_denuncia_target_event',
                $table,
                'target_event_id',
                '{{%event}}',
                'id',
                'CASCADE',
                'CASCADE'
            );
        }

        $this->createIndex('idx_denuncia_target_event_id', $table, 'target_event_id');
        $this->createIndex('idx_denuncia_target_type_target_event_id', $table, ['target_type', 'target_event_id']);
    }

    public function safeDown()
    {
        $table = '{{%denuncia}}';
        $rawTable = $this->db->getSchema()->getRawTableName($table);
        $schema = $this->db->schema->getTableSchema($rawTable, true);

        if ($schema === null || !isset($schema->columns['target_event_id'])) {
            return;
        }

        $this->dropForeignKey('fk_denuncia_target_event', $table);
        $this->dropIndex('idx_denuncia_target_type_target_event_id', $table);
        $this->dropIndex('idx_denuncia_target_event_id', $table);
        $this->dropColumn($table, 'target_event_id');
    }
}
