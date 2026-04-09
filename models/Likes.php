<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "likes".
 *
 * @property int $id_like
 * @property int $id_post
 * @property int $id_user
 * @property string|null $created_at
 *
 * @property Post $post
 * @property User $user
 */
class Likes extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'likes';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_post', 'id_user'], 'required'],
            [['id_post', 'id_user'], 'integer'],
            [['created_at'], 'safe'],
            [['id_post', 'id_user'], 'unique', 'targetAttribute' => ['id_post', 'id_user']],
            [['id_post'], 'exist', 'skipOnError' => true, 'targetClass' => Post::class, 'targetAttribute' => ['id_post' => 'id']],
            [['id_user'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['id_user' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_like' => 'Id Like',
            'id_post' => 'Id Post',
            'id_user' => 'Id User',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[Post]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPost()
    {
        return $this->hasOne(Post::class, ['id' => 'id_post']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'id_user']);
    }

}
