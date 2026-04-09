<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $user_id
 * @property string $diploma_pdf
 * @property string $estado
 * @property int|null $admin_user_id
 * @property string|null $observacao
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class BadgePedido extends ActiveRecord
{
    public const ESTADO_PENDENTE = 'pendente';
    public const ESTADO_APROVADO = 'aprovado';
    public const ESTADO_REJEITADO = 'rejeitado';

    public static function tableName()
    {
        return 'badge_pedido';
    }

    public function rules()
    {
        return [
            [['user_id', 'diploma_pdf'], 'required'],
            [['user_id', 'admin_user_id'], 'integer'],
            [['observacao'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['diploma_pdf'], 'string', 'max' => 255],
            [['estado'], 'in', 'range' => [self::ESTADO_PENDENTE, self::ESTADO_APROVADO, self::ESTADO_REJEITADO]],
        ];
    }
}
