<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "agua".
 *
 * @property int $id
 * @property int $quantidade_ml
 * @property string|null $data_registo
 * @property int $user_id
 *
 * @property User $user
 */
class Agua extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'agua';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['quantidade_ml', 'user_id'], 'required'],
            [['quantidade_ml', 'user_id'], 'integer'],
            [['quantidade_ml'], 'integer', 'min' => 1],
            [['data_registo'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'quantidade_ml' => 'Quantidade (ml)',
            'data_registo' => 'Data de Registo',
            'user_id' => 'User ID',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Get total water consumption for today
     */
    public static function getTodayTotal($userId)
    {
        return self::find()
            ->where(['user_id' => $userId])
            ->andWhere(['>=', 'DATE(data_registo)', date('Y-m-d')])
            ->andWhere(['<', 'DATE(data_registo)', date('Y-m-d', strtotime('+1 day'))])
            ->sum('quantidade_ml') ?? 0;
    }

    /**
     * Get recent water entries
     */
    public static function getRecentEntries($userId, $limit = 10)
    {
        return self::find()
            ->where(['user_id' => $userId])
            ->orderBy(['data_registo' => SORT_DESC])
            ->limit($limit)
            ->asArray()
            ->all();
    }
}
