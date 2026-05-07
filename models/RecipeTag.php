<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "recipe_tag".
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $created_at
 *
 * @property PlanoHasTag[] $planoHasTags
 * @property RecipeHasTag[] $recipeHasTags
 */
class RecipeTag extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'recipe_tag';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 100],
            [['name'], 'unique'],
            [['description'], 'string', 'max' => 255],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[PlanoHasTags]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlanoHasTags()
    {
        return $this->hasMany(PlanoHasTag::className(), ['tag_id' => 'id']);
    }

    /**
     * Gets query for [[RecipeHasTags]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecipeHasTags()
    {
        return $this->hasMany(RecipeHasTag::className(), ['tag_id' => 'id']);
    }
}
