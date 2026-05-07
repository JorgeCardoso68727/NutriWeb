<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $created_at
 */
class EventCategory extends ActiveRecord
{
    public static function tableName()
    {
        return 'event_category';
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['description', 'created_at'], 'safe'],
            [['name'], 'string', 'max' => 100],
            [['description'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Nome',
            'description' => 'Descrição',
            'created_at' => 'Data de Criação',
        ];
    }

    public function getEvents()
    {
        return $this->hasMany(Event::className(), ['category_id' => 'id']);
    }
}
