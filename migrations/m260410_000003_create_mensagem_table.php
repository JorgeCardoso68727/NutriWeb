<?php

use yii\db\Migration;

class m260410_000003_create_mensagem_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        if ($this->db->schema->getTableSchema('{{%mensagem}}', true) === null) {
            $this->createTable('{{%mensagem}}', [
                'id' => $this->primaryKey(),
                'remetente_id' => $this->integer()->notNull(),
                'destinatario_id' => $this->integer()->notNull(),
                'conteudo' => $this->text()->notNull(),
                'data_envio' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
                'lida' => $this->boolean()->defaultValue(0),
            ], $tableOptions);
        }

        if (!$this->indexExistsByColumns('mensagem', ['remetente_id'])) {
            $this->createIndex('idx_mensagem_remetente_id', '{{%mensagem}}', 'remetente_id');
        }

        if (!$this->indexExistsByColumns('mensagem', ['destinatario_id'])) {
            $this->createIndex('idx_mensagem_destinatario_id', '{{%mensagem}}', 'destinatario_id');
        }

        if (!$this->indexExistsByColumns('mensagem', ['data_envio'])) {
            $this->createIndex('idx_mensagem_data_envio', '{{%mensagem}}', 'data_envio');
        }

        if (!$this->indexExistsByColumns('mensagem', ['destinatario_id', 'lida'])) {
            $this->createIndex('idx_mensagem_destinatario_lida', '{{%mensagem}}', ['destinatario_id', 'lida']);
        }

        if (!$this->foreignKeyExists('fk_msg_remetente')) {
            $this->addForeignKey(
                'fk_msg_remetente',
                '{{%mensagem}}',
                'remetente_id',
                '{{%user}}',
                'id',
                'CASCADE',
                'CASCADE'
            );
        }

        if (!$this->foreignKeyExists('fk_msg_destinatario')) {
            $this->addForeignKey(
                'fk_msg_destinatario',
                '{{%mensagem}}',
                'destinatario_id',
                '{{%user}}',
                'id',
                'CASCADE',
                'CASCADE'
            );
        }
    }

    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('{{%mensagem}}', true) === null) {
            return;
        }

        if ($this->foreignKeyExists('fk_msg_destinatario')) {
            $this->dropForeignKey('fk_msg_destinatario', '{{%mensagem}}');
        }

        if ($this->foreignKeyExists('fk_msg_remetente')) {
            $this->dropForeignKey('fk_msg_remetente', '{{%mensagem}}');
        }

        if ($this->foreignKeyExists('fk_mensagem_receiver')) {
            $this->dropForeignKey('fk_mensagem_receiver', '{{%mensagem}}');
        }

        if ($this->foreignKeyExists('fk_mensagem_sender')) {
            $this->dropForeignKey('fk_mensagem_sender', '{{%mensagem}}');
        }

        $this->dropTable('{{%mensagem}}');
    }

    private function indexExistsByColumns($tableName, array $columns)
    {
        if ($this->db->driverName !== 'mysql') {
            return false;
        }

        $tableName = trim((string) $tableName);
        if ($tableName === '' || empty($columns)) {
            return false;
        }

        $placeholders = [];
        $params = [':tableName' => $tableName];
        foreach (array_values($columns) as $idx => $columnName) {
            $placeholder = ':col' . $idx;
            $placeholders[] = $placeholder;
            $params[$placeholder] = (string) $columnName;
        }

        $sql = 'SELECT index_name
                FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                  AND table_name = :tableName
                  AND column_name IN (' . implode(', ', $placeholders) . ')
                GROUP BY index_name
                HAVING COUNT(*) = :columnCount
                LIMIT 1';
        $params[':columnCount'] = count($columns);

        $result = $this->db->createCommand($sql, $params)->queryScalar();

        return (bool) $result;
    }

    private function foreignKeyExists($constraintName)
    {
        if ($this->db->driverName !== 'mysql') {
            return false;
        }

        $result = $this->db->createCommand(
            'SELECT 1 FROM information_schema.referential_constraints WHERE constraint_schema = DATABASE() AND constraint_name = :constraintName LIMIT 1',
            [':constraintName' => $constraintName]
        )->queryScalar();

        return (bool) $result;
    }
}
