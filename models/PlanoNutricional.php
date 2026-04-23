<?php

namespace app\models;

use amnah\yii2\user\models\User as ModuleUser;

/**
 * This is the model class for table "plano_nutricional".
 *
 * @property int $id
 * @property int $user_id
 * @property string $titulo
 * @property string|null $objetivo
 * @property string $descricao
 * @property string $estrutura_json
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property ModuleUser $user
 */
class PlanoNutricional extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'plano_nutricional';
    }

    public function rules()
    {
        return [
            [['user_id', 'titulo', 'descricao', 'estrutura_json'], 'required'],
            [['user_id'], 'integer'],
            [['descricao'], 'string'],
            [['estrutura_json'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['titulo', 'objetivo'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => ModuleUser::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'titulo' => 'Titulo',
            'objetivo' => 'Objetivo',
            'descricao' => 'Descricao',
            'estrutura_json' => 'Estrutura JSON',
            'created_at' => 'Criado em',
            'updated_at' => 'Atualizado em',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(ModuleUser::class, ['id' => 'user_id']);
    }
}
