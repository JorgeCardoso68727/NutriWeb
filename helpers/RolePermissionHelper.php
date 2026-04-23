<?php

namespace app\helpers;

use Yii;
use yii\db\Query;

class RolePermissionHelper
{

    // Verifica se a coluna de permissão existe na tabela de roles
    public static function rolePermissionColumnExists(string $permission): bool
    {
        $column = 'can_' . $permission;
        $roleSchema = Yii::$app->db->schema->getTableSchema('role', true);

        return $roleSchema !== null && isset($roleSchema->columns[$column]);
    }

    // Verifica se o utilizador tem a permissão especificada
    public static function hasRolePermission(int $userId, string $permission): bool
    {
        if ($userId <= 0) {
            return false;
        }

        if (!self::rolePermissionColumnExists($permission)) {
            return false;
        }

        $column = 'can_' . $permission;
        $value = (new Query())
            ->select(["r.$column"])
            ->from(['u' => 'user'])
            ->innerJoin(['r' => 'role'], 'r.id = u.role_id')
            ->where(['u.id' => $userId])
            ->scalar();

        return (int) $value === 1;
    }

    // Verifica se o utilizador atual é admin
    public static function isCurrentUserAdmin(): bool
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }

        return self::isUserAdmin((int) Yii::$app->user->id);
    }

    // Verifica se um utilizador especifico e admin
    public static function isUserAdmin(int $userId): bool
    {
        return self::hasRolePermission($userId, 'admin');
    }

    // Verifica se o utilizador é nutricionista
    public static function isUserNutritionist(int $userId): bool
    {
        return self::hasRolePermission($userId, 'nutricionista');
    }
}
