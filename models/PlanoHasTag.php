<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "plano_has_tag".
 *
 * @property int $plano_id
 * @property int $tag_id
 *
 * @property PlanoNutricional $plano
 * @property RecipeTag $tag
 */
class PlanoHasTag extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'plano_has_tag';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['plano_id', 'tag_id'], 'required'],
            [['plano_id', 'tag_id'], 'integer'],
            [['plano_id', 'tag_id'], 'unique', 'targetAttribute' => ['plano_id', 'tag_id']],
            [['plano_id'], 'exist', 'skipOnError' => true, 'targetClass' => PlanoNutricional::className(), 'targetAttribute' => ['plano_id' => 'id']],
            [['tag_id'], 'exist', 'skipOnError' => true, 'targetClass' => RecipeTag::className(), 'targetAttribute' => ['tag_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'plano_id' => 'Plano',
            'tag_id' => 'Tag',
        ];
    }

    /**
     * Gets query for [[Plano]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlano()
    {
        return $this->hasOne(PlanoNutricional::className(), ['id' => 'plano_id']);
    }

    /**
     * Gets query for [[Tag]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTag()
    {
        return $this->hasOne(RecipeTag::className(), ['id' => 'tag_id']);
    }
}
