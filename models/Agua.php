<?php

namespace app\models;

use Yii;

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
class Agua extends \yii\db\ActiveRecord
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
            [['data_registo'], 'safe'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'quantidade_ml' => 'Quantidade Ml',
            'data_registo' => 'Data Registo',
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

}
