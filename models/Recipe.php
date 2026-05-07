<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "recipe".
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string $instructions
 * @property int $creator_id
 * @property int|null $prep_time
 * @property int|null $cook_time
 * @property string|null $difficulty
 * @property int|null $servings
 * @property string|null $image
 * @property string|null $status
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property User $creator
 * @property RecipeHasTag[] $recipeHasTags
 * @property RecipeTag[] $tags
 */
class Recipe extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'recipe';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'instructions', 'creator_id'], 'required'],
            [['description', 'instructions'], 'string'],
            [['creator_id', 'prep_time', 'cook_time', 'servings'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['difficulty'], 'string', 'max' => 20],
            [['status'], 'string', 'max' => 20],
            [['image'], 'string', 'max' => 255],
            [['created_at', 'updated_at'], 'safe'],
            [['creator_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['creator_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'description' => 'Description',
            'instructions' => 'Instructions',
            'creator_id' => 'Creator',
            'prep_time' => 'Prep Time',
            'cook_time' => 'Cook Time',
            'difficulty' => 'Difficulty',
            'servings' => 'Servings',
            'image' => 'Image',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Creator]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreator()
    {
        return $this->hasOne(User::className(), ['id' => 'creator_id']);
    }

    /**
     * Gets query for [[RecipeHasTags]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecipeHasTags()
    {
        return $this->hasMany(RecipeHasTag::className(), ['recipe_id' => 'id']);
    }

    /**
     * Gets query for [[Tags]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        return $this->hasMany(RecipeTag::className(), ['id' => 'tag_id'])
            ->via('recipeHasTags');
    }
}
