<?php

namespace app\models;

use amnah\yii2\user\models\User as ModuleUser;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "mensagem".
 *
 * @property int $id
 * @property int $remetente_id
 * @property int $destinatario_id
 * @property string $conteudo
 * @property string|null $data_envio
 * @property int|null $lida
 */
class Mensagem extends ActiveRecord
{
    public static function tableName()
    {
        return 'mensagem';
    }

    public function rules()
    {
        return [
            [['remetente_id', 'destinatario_id', 'conteudo'], 'required'],
            [['remetente_id', 'destinatario_id', 'lida'], 'integer'],
            [['conteudo'], 'string'],
            [['data_envio'], 'safe'],
            [['remetente_id'], 'exist', 'skipOnError' => true, 'targetClass' => ModuleUser::class, 'targetAttribute' => ['remetente_id' => 'id']],
            [['destinatario_id'], 'exist', 'skipOnError' => true, 'targetClass' => ModuleUser::class, 'targetAttribute' => ['destinatario_id' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'remetente_id' => 'Remetente',
            'destinatario_id' => 'Destinatario',
            'conteudo' => 'Conteudo',
            'data_envio' => 'Data de envio',
            'lida' => 'Lida',
        ];
    }
}
