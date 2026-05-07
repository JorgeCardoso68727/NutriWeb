<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $event_id
 * @property int $user_id
 * @property string|null $status
 * @property string|null $registered_at
 */
class EventParticipant extends ActiveRecord
{
    public static function tableName()
    {
        return 'event_participant';
    }

    public static function primaryKey()
    {
        return ['event_id', 'user_id'];
    }

    public function rules()
    {
        return [
            [['event_id', 'user_id'], 'required'],
            [['event_id', 'user_id'], 'integer'],
            [['registered_at'], 'safe'],
            [['status'], 'string', 'max' => 20],
            [['event_id', 'user_id'], 'unique', 'targetAttribute' => ['event_id', 'user_id']],
            [['event_id'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Event::className(), 'targetAttribute' => ['event_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => \amnah\yii2\user\models\User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'event_id' => 'Evento',
            'user_id' => 'Utilizador',
            'status' => 'Estado',
            'registered_at' => 'Registado em',
        ];
    }

    public function getEvent()
    {
        return $this->hasOne(\app\models\Event::className(), ['id' => 'event_id']);
    }

    public function getUser()
    {
        return $this->hasOne(\amnah\yii2\user\models\User::className(), ['id' => 'user_id']);
    }
}
