<?php

namespace app\controllers;

use Yii;
use app\models\Event;
use app\models\EventCategory;
use app\models\EventParticipant;
use app\helpers\RolePermissionHelper;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * EventController implements the CRUD actions for Event model.
 */
class EventController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Event models with filters.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        Event::completeExpiredEvents();

        $query = Event::find()->where(['status' => 'active']);

        // Filter by category
        if (Yii::$app->request->get('category_id')) {
            $query->andWhere(['category_id' => Yii::$app->request->get('category_id')]);
        }

        // Filter by location
        if (Yii::$app->request->get('location')) {
            $query->andWhere(['like', 'location', Yii::$app->request->get('location')]);
        }

        // Filter by date range
        if (Yii::$app->request->get('start_date')) {
            $query->andWhere(['>=', 'start_date', Yii::$app->request->get('start_date')]);
        }

        if (Yii::$app->request->get('end_date')) {
            $query->andWhere(['<=', 'start_date', Yii::$app->request->get('end_date')]);
        }

        // Filter by availability
        if (Yii::$app->request->get('has_space')) {
            $query->andWhere([
                'or',
                ['is', 'max_participants', null],
                ['>', 'max_participants', new \yii\db\Expression('(SELECT COUNT(*) FROM event_participant WHERE event_id = event.id)')]
            ]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query->with(['category', 'creator']),
            'pagination' => [
                'pageSize' => 12,
            ],
            'sort' => [
                'defaultOrder' => ['start_date' => SORT_ASC],
            ],
        ]);

        $categories = EventCategory::find()->all();

        // Get events the current user is registered for (if not guest)
        $registeredEvents = [];
        if (!Yii::$app->user->isGuest) {
            $registeredEvents = Event::find()
                ->innerJoin('event_participant', 'event_participant.event_id = event.id')
                ->where(['event_participant.user_id' => Yii::$app->user->id, 'event_participant.status' => 'registered'])
                ->orderBy(['event.start_date' => SORT_ASC])
                ->all();
        }

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'categories' => $categories,
            'canCreateEvent' => $this->canCreateEvents(),
            'registeredEvents' => $registeredEvents,
            'filterModel' => [
                'category_id' => Yii::$app->request->get('category_id'),
                'location' => Yii::$app->request->get('location'),
                'start_date' => Yii::$app->request->get('start_date'),
                'end_date' => Yii::$app->request->get('end_date'),
                'has_space' => Yii::$app->request->get('has_space'),
            ],
        ]);
    }

    /**
     * Displays a single Event model.
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        Event::completeExpiredEvents();

        $model = $this->findModel($id);

        // Get participant count
        $participantCount = EventParticipant::find()
            ->where(['event_id' => $id, 'status' => 'registered'])
            ->count();

        // Check if current user is registered
        $isRegistered = false;
        if (!Yii::$app->user->isGuest) {
            $isRegistered = EventParticipant::find()
                ->where(['event_id' => $id, 'user_id' => Yii::$app->user->id])
                ->exists();
        }

        return $this->render('view', [
            'model' => $model,
            'participantCount' => $participantCount,
            'isRegistered' => $isRegistered,
            'canEditEvent' => $this->canEditEvent($model),
            'canDeleteEvent' => $this->canDeleteEvent($model),
            'isAdminViewer' => RolePermissionHelper::isCurrentUserAdmin(),
            'canReportEvent' => !Yii::$app->user->isGuest && (int) Yii::$app->user->id !== (int) $model->creator_id,
        ]);
    }

    /**
     * Creates a new Event model.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        if (!$this->canCreateEvents()) {
            throw new \yii\web\ForbiddenHttpException('Você não tem permissão para criar eventos.');
        }

        $model = new Event();
        $model->creator_id = Yii::$app->user->id;
        $model->status = 'active';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        if ($model->start_date) {
            $model->start_date = substr((string) $model->start_date, 0, 16);
        }
        if ($model->end_date) {
            $model->end_date = substr((string) $model->end_date, 0, 16);
        }

        $categories = EventCategory::find()->all();

        return $this->render('create', [
            'model' => $model,
            'categories' => $categories,
        ]);
    }

    /**
     * Updates an existing Event model.
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (!$this->canEditEvent($model)) {
            throw new \yii\web\ForbiddenHttpException('Você não tem permissão para editar este evento.');
        }

        // Store original values before loading new data
        $oldValues = [
            'title' => $model->title,
            'description' => $model->description,
            'start_date' => $model->start_date,
            'end_date' => $model->end_date,
            'location' => $model->location,
            'max_participants' => $model->max_participants,
        ];

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // Check for changes and notify participants
            $changedFields = $this->detectEventChanges($oldValues, $model);
            if (!empty($changedFields)) {
                $this->notifyParticipantsOfChanges($model, $changedFields);
            }

            return $this->redirect(['view', 'id' => $model->id]);
        }

        if ($model->start_date) {
            $model->start_date = substr((string) $model->start_date, 0, 16);
        }
        if ($model->end_date) {
            $model->end_date = substr((string) $model->end_date, 0, 16);
        }

        $categories = EventCategory::find()->all();

        return $this->render('update', [
            'model' => $model,
            'categories' => $categories,
        ]);
    }

    /**
     * Deletes an existing Event model.
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if (!$this->canDeleteEvent($model)) {
            throw new \yii\web\ForbiddenHttpException('Você não tem permissão para eliminar este evento.');
        }

        $model->delete();

        return $this->redirect(['index']);
    }

    /**
     * Register user for an event.
     *
     * @param integer $id Event ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionRegister($id)
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['/site/login']);
        }

        $model = $this->findModel($id);
        if ($model->status === 'completed') {
            Yii::$app->session->setFlash('error', 'Não é possível inscrever-se num evento concluído.');
            return $this->redirect(['view', 'id' => $id]);
        }

        $userId = Yii::$app->user->id;

        // Check if already registered
        $existing = EventParticipant::find()
            ->where(['event_id' => $id, 'user_id' => $userId])
            ->one();

        if ($existing) {
            Yii::$app->session->setFlash('error', 'Você já está registado neste evento.');
            return $this->redirect(['view', 'id' => $id]);
        }

        // Check if has space
        if ($model->max_participants) {
            $count = EventParticipant::find()
                ->where(['event_id' => $id, 'status' => 'registered'])
                ->count();
            if ($count >= $model->max_participants) {
                Yii::$app->session->setFlash('error', 'O evento está cheio.');
                return $this->redirect(['view', 'id' => $id]);
            }
        }

        $participant = new EventParticipant();
        $participant->event_id = $id;
        $participant->user_id = $userId;
        $participant->status = 'registered';

        if ($participant->save()) {
            // Send confirmation email
            $user = Yii::$app->user->identity;
            $eventUrl = Yii::$app->urlManager->createAbsoluteUrl(['/event/view', 'id' => $id]);

            try {
                Yii::$app->mailer->compose()
                    ->setTo($user->email)
                    ->setFrom(Yii::$app->params['senderEmail'])
                    ->setSubject('Inscrição confirmada: ' . Html::encode($model->title))
                    ->setHtmlBody($this->renderPartial('//email/event-registration', [
                        'user' => $user,
                        'event' => $model,
                        'eventUrl' => $eventUrl,
                    ]))
                    ->send();
            } catch (\Exception $e) {
                Yii::warning('Failed to send event registration email: ' . $e->getMessage());
            }

            Yii::$app->session->setFlash('success', 'Registado com sucesso no evento! Um email de confirmação foi enviado.');
        } else {
            Yii::$app->session->setFlash('error', 'Erro ao registar no evento.');
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Unregister user from an event.
     *
     * @param integer $id Event ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUnregister($id)
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['/site/login']);
        }

        $model = $this->findModel($id);
        $userId = Yii::$app->user->id;

        $participant = EventParticipant::findOne([
            'event_id' => $id,
            'user_id' => $userId
        ]);

        if ($participant) {
            $participant->delete();
            Yii::$app->session->setFlash('success', 'Removido do evento com sucesso!');
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Mark event as completed and notify all participants.
     *
     * @param integer $id Event ID
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionComplete($id)
    {
        $model = $this->findModel($id);

        if (!$this->canEditEvent($model)) {
            throw new \yii\web\ForbiddenHttpException('Você não tem permissão para completar este evento.');
        }

        if ($model->status !== 'completed') {
            $model->status = 'completed';
            $model->save(false);
            $this->notifyParticipantsCompletion($model);
            Yii::$app->session->setFlash('success', 'Evento marcado como concluído e participantes foram notificados!');
        } else {
            Yii::$app->session->setFlash('info', 'Este evento já foi marcado como concluído.');
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Finds the Event model based on its primary key value.
     *
     * @param integer $id
     * @return Event the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Event::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Página não encontrada.');
    }

    private function canCreateEvents(): bool
    {
        return !Yii::$app->user->isGuest
            && RolePermissionHelper::isUserInstitution((int) Yii::$app->user->id);
    }

    private function canEditEvent(Event $model): bool
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }

        return (int) $model->creator_id === (int) Yii::$app->user->id
            && RolePermissionHelper::isUserInstitution((int) Yii::$app->user->id);
    }

    private function canDeleteEvent(Event $model): bool
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }

        return RolePermissionHelper::isCurrentUserAdmin() || $this->canEditEvent($model);
    }

    /**
     * Detect changes between old and new event values.
     *
     * @param array $oldValues Original values
     * @param Event $model Updated event model
     * @return array Changed fields with old/new values
     */
    private function detectEventChanges(array $oldValues, Event $model): array
    {
        $changedFields = [];
        $fieldLabels = [
            'title' => 'Título',
            'description' => 'Descrição',
            'start_date' => 'Data de Início',
            'end_date' => 'Data de Término',
            'location' => 'Localização',
            'max_participants' => 'Participantes Máximos',
        ];

        foreach ($oldValues as $field => $oldValue) {
            $newValue = $model->{$field};
            if ((string) $oldValue !== (string) $newValue) {
                $changedFields[$fieldLabels[$field] ?? $field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changedFields;
    }

    /**
     * Notify all participants that the event has been updated.
     *
     * @param Event $event Event model
     * @param array $changedFields Changed fields
     */
    private function notifyParticipantsOfChanges(Event $event, array $changedFields): void
    {
        try {
            $participants = EventParticipant::find()
                ->where(['event_id' => $event->id, 'status' => 'registered'])
                ->with('user')
                ->all();

            $eventUrl = Yii::$app->urlManager->createAbsoluteUrl(['/event/view', 'id' => $event->id]);

            foreach ($participants as $participant) {
                if ($participant->user && $participant->user->email) {
                    try {
                        Yii::$app->mailer->compose('//email/event-updated', [
                            'user' => $participant->user,
                            'event' => $event,
                            'eventUrl' => $eventUrl,
                            'changedFields' => $changedFields,
                        ])
                            ->setTo($participant->user->email)
                            ->setFrom(Yii::$app->params['senderEmail'])
                            ->setSubject('Evento atualizado: ' . Html::encode($event->title))
                            ->send();
                    } catch (\Exception $e) {
                        Yii::warning('Failed to send event update email to ' . $participant->user->email . ': ' . $e->getMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            Yii::warning('Failed to notify participants of event changes: ' . $e->getMessage());
        }
    }

    /**
     * Notify all participants that the event has been completed.
     *
     * @param Event $event Event model
     */
    private function notifyParticipantsCompletion(Event $event): void
    {
        try {
            $participants = EventParticipant::find()
                ->where(['event_id' => $event->id, 'status' => 'registered'])
                ->with('user')
                ->all();

            $eventUrl = Yii::$app->urlManager->createAbsoluteUrl(['/event/view', 'id' => $event->id]);

            foreach ($participants as $participant) {
                if ($participant->user && $participant->user->email) {
                    try {
                        Yii::$app->mailer->compose('//email/event-completed', [
                            'user' => $participant->user,
                            'event' => $event,
                            'eventUrl' => $eventUrl,
                        ])
                            ->setTo($participant->user->email)
                            ->setFrom(Yii::$app->params['senderEmail'])
                            ->setSubject('Evento concluído: ' . Html::encode($event->title))
                            ->send();
                    } catch (\Exception $e) {
                        Yii::warning('Failed to send event completion email to ' . $participant->user->email . ': ' . $e->getMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            Yii::warning('Failed to notify participants of event completion: ' . $e->getMessage());
        }
    }
}
