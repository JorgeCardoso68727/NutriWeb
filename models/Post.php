<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "post".
 *
 * @property int $id
 * @property string $titulo
 * @property string $conteudo
 * @property string|null $imagem
 * @property string|null $data_criacao
 * @property int $user_id
 * @property string|null $CorPost
 *
 * @property User $user
 */
class Post extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'post';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['imagem'], 'default', 'value' => null],
            [['titulo', 'conteudo', 'user_id'], 'required'],
            [['conteudo'], 'string'],
            [['data_criacao'], 'safe'],
            [['user_id'], 'integer'],
            [['titulo', 'imagem'], 'string', 'max' => 255],
            [['CorPost'], 'string', 'max' => 7],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'titulo' => 'Titulo',
            'conteudo' => 'Conteudo',
            'imagem' => 'Imagem',
            'data_criacao' => 'Data Criacao',
            'user_id' => 'User ID',
            'CorPost' => 'Cor do Post',
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
