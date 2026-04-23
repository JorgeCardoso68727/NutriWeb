<?php

namespace app\models;

use yii\db\ActiveRecord;

class Denuncia extends ActiveRecord
{
    public const ESTADO_REVISAO_PENDENTE = 'pendente';
    public const ESTADO_REVISAO_REVISTO = 'revisto';

    public static function tableName()
    {
        return '{{%denuncia}}';
    }

    public function rules()
    {
        return [
            [['target_user_id', 'motivo'], 'required'],
            [['target_user_id', 'autor_id', 'target_post_id'], 'integer'],
            [['descricao'], 'string'],
            [['data_denuncia'], 'safe'],
            [['target_type', 'estado_revisao'], 'string', 'max' => 20],
            [['motivo'], 'string', 'max' => 100],
            [['estado_revisao'], 'in', 'range' => [self::ESTADO_REVISAO_PENDENTE, self::ESTADO_REVISAO_REVISTO]],
        ];
    }
}
