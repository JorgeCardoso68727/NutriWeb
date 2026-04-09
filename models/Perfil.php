<?php

namespace app\models;

use amnah\yii2\user\models\User as ModuleUser;
use Yii;

/**
 * This is the model class for table "perfil".
 *
 * @property int $id
 * @property int $user_id
 * @property string $Frist_Name
 * @property string $Last_Name
 * @property int $Bio
 * @property int $Foto
 * @property int $Telefone
 * @property User $user
 */
class Perfil extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'perfil';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Frist_Name', 'Last_Name'], 'required'],
            [['Bio'], 'default', 'value' => ''],
            [['user_id', 'Telefone'], 'integer'],
            [['Bio', 'Foto'], 'string', 'max' => 255],
            [['Frist_Name', 'Last_Name'], 'string', 'max' => 25],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => ModuleUser::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'Frist_Name' => 'Frist Name',
            'Last_Name' => 'Last Name',
            'Bio' => 'Bio',
            'Foto' => 'Foto',
            'Telefone' => 'Telefone',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(ModuleUser::class, ['id' => 'user_id']);
    }

    public function beforeSave($insert)
    {
        if ($this->Bio === null) {
            $this->Bio = '';
        }
        if ($this->isNewRecord && !$this->Foto) {
            $this->Foto = 'img/default.jpeg';
        }
        return parent::beforeSave($insert);
    }

    public function setUser($userId)
    {
        $this->user_id = $userId;
        return $this;
    }
}
