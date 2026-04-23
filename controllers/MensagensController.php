<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\Mensagem;
use amnah\yii2\user\models\User;

class MensagensController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'mensagens-updates' => ['get', 'post'],
                ],
            ],
        ];
    }

    /**
     * Displays the messages page
     */
    public function actionMensagens()
    {
        $currentUserId = Yii::$app->user->id;
        if (!$currentUserId) {
            return $this->redirect(['site/login']);
        }

        // Get search term from query parameter
        $userSearchTerm = Yii::$app->request->get('u', '');
        $userSearchTerm = trim((string) $userSearchTerm);

        // Get the "with" parameter to determine which conversation is selected
        $withUsername = Yii::$app->request->get('with', '');
        $withUsername = trim((string) $withUsername);

        $selectedUser = null;
        $messages = [];
        $conversationUsers = [];
        $conversationMetaByUserId = [];

        // Search for users if search term is provided
        $userSearchResults = [];
        if ($userSearchTerm !== '') {
            $userSearchResults = User::find()
                ->where(['not', ['id' => $currentUserId]])
                ->andWhere(['like', 'username', '%' . $userSearchTerm . '%'])
                ->limit(20)
                ->all();

            // Format user results for display
            $userSearchResults = array_map(function ($user) {
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'Frist_Name' => $user->profile->Frist_Name ?? '',
                    'Last_Name' => $user->profile->Last_Name ?? '',
                    'profile_photo' => $user->profile->Foto ?? 'img/default.jpeg',
                ];
            }, $userSearchResults);
        }

        // Get all users with active conversations
        $conversationUserIds = Yii::$app->db->createCommand(
            'SELECT user_id FROM (
                SELECT CASE 
                    WHEN remetente_id = :userId THEN destinatario_id 
                    ELSE remetente_id 
                END as user_id,
                MAX(data_envio) as last_message_date
                FROM mensagem 
                WHERE remetente_id = :userId OR destinatario_id = :userId 
                GROUP BY CASE 
                    WHEN remetente_id = :userId THEN destinatario_id 
                    ELSE remetente_id 
                END
                ORDER BY last_message_date DESC
            ) as t'
        )->bindValues([':userId' => $currentUserId])->queryColumn();

        if (!empty($conversationUserIds)) {
            $conversationUsers = User::findAll(['id' => $conversationUserIds]);

            // Build meta data (unread count, last message, etc.)
            foreach ($conversationUsers as $user) {
                $unreadCount = Mensagem::find()
                    ->where(['destinatario_id' => $currentUserId])
                    ->andWhere(['remetente_id' => $user->id])
                    ->andWhere(['lida' => 0])
                    ->count();

                $lastMessage = Mensagem::find()
                    ->where(['or',
                        ['and', ['remetente_id' => $currentUserId], ['destinatario_id' => $user->id]],
                        ['and', ['remetente_id' => $user->id], ['destinatario_id' => $currentUserId]],
                    ])
                    ->orderBy(['data_envio' => SORT_DESC])
                    ->one();

                $conversationMetaByUserId[$user->id] = [
                    'unread_count' => $unreadCount,
                    'last_message_preview' => $lastMessage ? mb_substr($lastMessage->conteudo, 0, 100) : '',
                    'last_message_at' => $lastMessage ? $lastMessage->data_envio : '',
                ];
            }

            // Format conversation users for display
            $conversationUsers = array_map(function ($user) {
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'Frist_Name' => $user->profile->Frist_Name ?? '',
                    'Last_Name' => $user->profile->Last_Name ?? '',
                    'profile_photo' => $user->profile->Foto ?? 'img/default.jpeg',
                ];
            }, $conversationUsers);
        }

        // If a conversation is selected, load messages and user details
        if ($withUsername !== '') {
            $withUser = User::findOne(['username' => $withUsername]);
            if ($withUser && (int) $withUser->id !== $currentUserId) {
                $selectedUser = [
                    'id' => $withUser->id,
                    'username' => $withUser->username,
                    'Frist_Name' => $withUser->profile->Frist_Name ?? '',
                    'Last_Name' => $withUser->profile->Last_Name ?? '',
                    'profile_photo' => $withUser->profile->Foto ?? 'img/default.jpeg',
                ];

                // Load messages for this conversation
                $messages = Mensagem::find()
                    ->where(['or',
                        ['and', ['remetente_id' => $currentUserId], ['destinatario_id' => $withUser->id]],
                        ['and', ['remetente_id' => $withUser->id], ['destinatario_id' => $currentUserId]],
                    ])
                    ->orderBy(['data_envio' => SORT_ASC])
                    ->all();

                // Format messages for display
                $messages = array_map(function ($msg) use ($currentUserId) {
                    return [
                        'id' => $msg->id,
                        'sender_id' => $msg->remetente_id,
                        'conteudo' => $msg->conteudo,
                        'created_at' => $msg->data_envio,
                        'lida' => $msg->lida,
                        'attachment_url' => '',
                    ];
                }, $messages);

                // Mark messages as read
                Mensagem::updateAll(
                    ['lida' => 1],
                    ['and', ['destinatario_id' => $currentUserId], ['remetente_id' => $withUser->id]]
                );
            }
        }

        return $this->render('@app/views/user/default/mensagens', [
            'currentUserId' => $currentUserId,
            'userSearchTerm' => $userSearchTerm,
            'userSearchResults' => $userSearchResults,
            'conversationUsers' => $conversationUsers,
            'conversationMetaByUserId' => $conversationMetaByUserId,
            'selectedUser' => $selectedUser,
            'messages' => $messages,
        ]);
    }

    /**
     * Sends a new message (AJAX endpoint)
     */
    public function actionEnviarMensagem()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (Yii::$app->request->isPost === false) {
            return ['success' => false, 'error' => 'Invalid request method'];
        }

        $currentUserId = Yii::$app->user->id;
        if (!$currentUserId) {
            return ['success' => false, 'error' => 'Not authenticated'];
        }

        $targetUserId = (int) (Yii::$app->request->post('target_user_id', 0));
        $conteudo = trim((string) (Yii::$app->request->post('conteudo', '')));

        if ($targetUserId <= 0 || empty($conteudo)) {
            return ['success' => false, 'error' => 'Missing required fields'];
        }

        if ($targetUserId === $currentUserId) {
            return ['success' => false, 'error' => 'Cannot send message to yourself'];
        }

        // Verify target user exists
        $targetUser = User::findOne($targetUserId);
        if (!$targetUser) {
            return ['success' => false, 'error' => 'Target user not found'];
        }

        // Save the message
        $message = new Mensagem();
        $message->remetente_id = $currentUserId;
        $message->destinatario_id = $targetUserId;
        $message->conteudo = $conteudo;

        if (!$message->save()) {
            return ['success' => false, 'error' => 'Failed to save message'];
        }

        // Trigger a page reload to show the new message
        return ['success' => true];
    }

    /**
     * Gets message updates (AJAX endpoint for polling)
     */
    public function actionMensagensUpdates()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $currentUserId = Yii::$app->user->id;
        if (!$currentUserId) {
            return ['success' => false];
        }

        $withUsername = Yii::$app->request->get('with', '');
        $withUsername = trim((string) $withUsername);

        if (empty($withUsername)) {
            return ['success' => false];
        }

        $withUser = User::findOne(['username' => $withUsername]);
        if (!$withUser || (int) $withUser->id === $currentUserId) {
            return ['success' => false];
        }

        // Get messages
        $messages = Mensagem::find()
            ->where(['or',
                ['and', ['remetente_id' => $currentUserId], ['destinatario_id' => $withUser->id]],
                ['and', ['remetente_id' => $withUser->id], ['destinatario_id' => $currentUserId]],
            ])
            ->orderBy(['data_envio' => SORT_ASC])
            ->all();

        // Format messages
        $formattedMessages = array_map(function ($msg) use ($currentUserId) {
            return [
                'id' => $msg->id,
                'sender_id' => $msg->remetente_id,
                'conteudo' => $msg->conteudo,
                'created_at' => $msg->data_envio,
                'lida' => $msg->lida,
                'attachment_url' => '',
            ];
        }, $messages);

        // Mark messages as read
        Mensagem::updateAll(
            ['lida' => 1],
            ['and', ['destinatario_id' => $currentUserId], ['remetente_id' => $withUser->id]]
        );

        // Get unread counts for all users
        $conversationUserIds = Yii::$app->db->createCommand(
            'SELECT CASE 
                WHEN remetente_id = :userId THEN destinatario_id 
                ELSE remetente_id 
            END as user_id 
            FROM mensagem 
            WHERE remetente_id = :userId OR destinatario_id = :userId
            GROUP BY CASE 
                WHEN remetente_id = :userId THEN destinatario_id 
                ELSE remetente_id 
            END'
        )->bindValues([':userId' => $currentUserId])->queryColumn();

        $conversationMetaByUserId = [];
        if (!empty($conversationUserIds)) {
            foreach ($conversationUserIds as $userId) {
                $unreadCount = Mensagem::find()
                    ->where(['destinatario_id' => $currentUserId])
                    ->andWhere(['remetente_id' => $userId])
                    ->andWhere(['lida' => 0])
                    ->count();

                $conversationMetaByUserId[$userId] = [
                    'unread_count' => $unreadCount,
                ];
            }
        }

        return [
            'success' => true,
            'messages' => $formattedMessages,
            'conversationMetaByUserId' => $conversationMetaByUserId,
        ];
    }
}
