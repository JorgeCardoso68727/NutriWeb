<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "recipe_has_tag".
 *
 * @property int $recipe_id
 * @property int $tag_id
 *
 * @property Recipe $recipe
 * @property RecipeTag $tag
 */
class RecipeHasTag extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'recipe_has_tag';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['recipe_id', 'tag_id'], 'required'],
            [['recipe_id', 'tag_id'], 'integer'],
            [['recipe_id', 'tag_id'], 'unique', 'targetAttribute' => ['recipe_id', 'tag_id']],
            [['recipe_id'], 'exist', 'skipOnError' => true, 'targetClass' => Recipe::className(), 'targetAttribute' => ['recipe_id' => 'id']],
            [['tag_id'], 'exist', 'skipOnError' => true, 'targetClass' => RecipeTag::className(), 'targetAttribute' => ['tag_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'recipe_id' => 'Recipe',
            'tag_id' => 'Tag',
        ];
    }

    /**
     * Gets query for [[Recipe]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecipe()
    {
        return $this->hasOne(Recipe::className(), ['id' => 'recipe_id']);
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
