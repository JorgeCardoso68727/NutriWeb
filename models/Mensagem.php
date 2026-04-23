<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "mensagem".
 *
 * @property int $id
 * @property int $remetente_id
 * @property int $destinatario_id
 * @property string $conteudo
 * @property string $data_envio
 * @property int|null $lida
 */
class Mensagem extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mensagem';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['remetente_id', 'destinatario_id', 'conteudo'], 'required'],
            [['remetente_id', 'destinatario_id', 'lida'], 'integer'],
            [['conteudo'], 'string'],
            [['data_envio'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'remetente_id' => 'Remetente ID',
            'destinatario_id' => 'Destinatário ID',
            'conteudo' => 'Conteúdo',
            'data_envio' => 'Data de Envio',
            'lida' => 'Lida',
        ];
    }

    /**
     * Gets the sender user
     */
    public function getRemetente()
    {
        return $this->hasOne(User::class, ['id' => 'remetente_id']);
    }

    /**
     * Gets the recipient user
     */
    public function getDestinatario()
    {
        return $this->hasOne(User::class, ['id' => 'destinatario_id']);
    }
}
