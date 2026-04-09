<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "seguidor".
 *
 * @property int $seguidor_id
 * @property int $seguido_id
 * @property string|null $data_seguimento
 */
class Seguidor extends ActiveRecord
{
    public static function tableName()
    {
        return 'seguidor';
    }

    public static function isFollowing($seguidorId, $seguidoId)
    {
        if ((int) $seguidorId === (int) $seguidoId) {
            return false;
        }

        return (bool) (new \yii\db\Query())
            ->from(static::tableName())
            ->where([
                'seguidor_id' => (int) $seguidorId,
                'seguido_id' => (int) $seguidoId,
            ])
            ->exists();
    }

    public static function follow($seguidorId, $seguidoId)
    {
        $seguidorId = (int) $seguidorId;
        $seguidoId = (int) $seguidoId;

        if ($seguidorId === $seguidoId || static::isFollowing($seguidorId, $seguidoId)) {
            return false;
        }

        return (bool) Yii::$app->db->createCommand()->insert(static::tableName(), [
            'seguidor_id' => $seguidorId,
            'seguido_id' => $seguidoId,
            'data_seguimento' => date('Y-m-d H:i:s'),
        ])->execute();
    }

    public static function unfollow($seguidorId, $seguidoId)
    {
        return (bool) Yii::$app->db->createCommand()->delete(static::tableName(), [
            'seguidor_id' => (int) $seguidorId,
            'seguido_id' => (int) $seguidoId,
        ])->execute();
    }

    public static function countFollowers($userId)
    {
        return (int) (new \yii\db\Query())
            ->from(static::tableName())
            ->where(['seguido_id' => (int) $userId])
            ->count();
    }

    public static function countFollowing($userId)
    {
        return (int) (new \yii\db\Query())
            ->from(static::tableName())
            ->where(['seguidor_id' => (int) $userId])
            ->count();
    }
}
