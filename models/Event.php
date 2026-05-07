<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $title
 * @property string $description
 * @property int $category_id
 * @property int $creator_id
 * @property string|null $location
 * @property string $start_date
 * @property string|null $end_date
 * @property string|null $image
 * @property int|null $max_participants
 * @property string|null $status
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Event extends ActiveRecord
{
    public static function tableName()
    {
        return 'event';
    }

    public function rules()
    {
        return [
            [['title', 'description', 'category_id', 'creator_id', 'start_date'], 'required'],
            [['description'], 'string'],
            [['category_id', 'creator_id', 'max_participants'], 'integer'],
            [['start_date', 'end_date', 'created_at', 'updated_at'], 'safe'],
            [['title', 'location', 'image', 'status'], 'string', 'max' => 255],
            [['start_date'], 'validateStartDate'],
            [['end_date'], 'validateEndDate'],
        ];
    }

    public function validateStartDate($attribute, $params)
    {
        $startDate = trim((string) ($this->$attribute ?? ''));
        if ($startDate === '') {
            return;
        }

        if (!$this->isNewRecord) {
            return;
        }

        $normalizedDate = $this->normalizeDateTimeInput($startDate);
        if ($normalizedDate === null) {
            $this->addError($attribute, 'Data de início inválida.');
            return;
        }

        try {
            $startDateTime = new \DateTime($normalizedDate, new \DateTimeZone('UTC'));
            $now = new \DateTime('now', new \DateTimeZone('UTC'));

            if ($startDateTime < $now) {
                $this->addError($attribute, 'A data de início não pode ser anterior à data e hora atual.');
            }
        } catch (\Exception $e) {
            $this->addError($attribute, 'Data de início inválida.');
        }
    }

    public function validateEndDate($attribute, $params)
    {
        $endDate = trim((string) ($this->$attribute ?? ''));
        if ($endDate === '') {
            return;
        }

        $startDate = trim((string) ($this->start_date ?? ''));
        if ($startDate === '') {
            return;
        }

        $normalizedEndDate = $this->normalizeDateTimeInput($endDate);
        $normalizedStartDate = $this->normalizeDateTimeInput($startDate);

        if ($normalizedEndDate === null || $normalizedStartDate === null) {
            return;
        }

        try {
            $endDateTime = new \DateTime($normalizedEndDate, new \DateTimeZone('UTC'));
            $startDateTime = new \DateTime($normalizedStartDate, new \DateTimeZone('UTC'));

            if ($endDateTime <= $startDateTime) {
                $this->addError($attribute, 'A data de término deve ser posterior à data de início.');
            }
        } catch (\Exception $e) {
            // Invalid date format, let other validators handle it
        }
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Título',
            'description' => 'Descrição',
            'category_id' => 'Categoria',
            'creator_id' => 'Criador',
            'location' => 'Localização',
            'start_date' => 'Data e Hora de Início',
            'end_date' => 'Data e Hora de Fim',
            'image' => 'Imagem',
            'max_participants' => 'Número Máximo de Participantes',
            'status' => 'Estado',
            'created_at' => 'Criado em',
            'updated_at' => 'Atualizado em',
        ];
    }

    public function getCategory()
    {
        return $this->hasOne(\app\models\EventCategory::className(), ['id' => 'category_id']);
    }

    public function getCreator()
    {
        return $this->hasOne(\amnah\yii2\user\models\User::className(), ['id' => 'creator_id']);
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->start_date = $this->normalizeDateTimeInput($this->start_date);
        $this->end_date = $this->normalizeDateTimeInput($this->end_date);

        return true;
    }

    public static function completeExpiredEvents(): int
    {
        $now = gmdate('Y-m-d H:i:s');

        $expiredEvents = self::find()
            ->where(['status' => 'active'])
            ->andWhere(['<=', 'end_date', $now])
            ->all();

        $completedCount = 0;

        foreach ($expiredEvents as $event) {
            $event->status = 'completed';
            if ($event->save(false, ['status', 'updated_at'])) {
                $completedCount++;
            }
        }

        return $completedCount;
    }

    private function normalizeDateTimeInput($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $value = str_replace('T', ' ', $value);
        if (strlen($value) === 16) {
            $value .= ':00';
        }

        return $value;
    }
}
