<?php

use yii\db\Migration;

class m260410_000003_add_role_permissions_columns extends Migration
{
    public function safeUp()
    {
        $table = '{{%role}}';
        $schema = $this->db->schema->getTableSchema($this->db->getSchema()->getRawTableName($table), true);

        if ($schema === null) {
            return;
        }

        if (!isset($schema->columns['can_nutricionista'])) {
            $this->addColumn($table, 'can_nutricionista', $this->smallInteger()->notNull()->defaultValue(0));
        }

        if (!isset($schema->columns['can_instituicao'])) {
            $this->addColumn($table, 'can_instituicao', $this->smallInteger()->notNull()->defaultValue(0));
        }

        // Seed permissions based on current role names.
        $this->update($table, ['can_nutricionista' => 1], ['name' => 'Nutricionista']);
        $this->update($table, ['can_instituicao' => 1], ['name' => 'Instituicao']);
    }

    public function safeDown()
    {
        $table = '{{%role}}';
        $schema = $this->db->schema->getTableSchema($this->db->getSchema()->getRawTableName($table), true);

        if ($schema === null) {
            return;
        }

        if (isset($schema->columns['can_instituicao'])) {
            $this->dropColumn($table, 'can_instituicao');
        }

        if (isset($schema->columns['can_nutricionista'])) {
            $this->dropColumn($table, 'can_nutricionista');
        }
    }
}
