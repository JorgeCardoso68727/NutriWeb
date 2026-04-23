<?php

namespace app\controllers;

use app\models\Post;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\web\Controller;

class ProcurarController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $term = trim((string) Yii::$app->request->get('q', ''));
        $users = [];
        $posts = [];

        if ($term !== '') {
            $users = (new Query())
                ->select([
                    'u.id',
                    'u.username',
                    'pr.Frist_Name',
                    'pr.Last_Name',
                    'pr.Bio',
                    'pr.Foto AS profile_photo',
                ])
                ->from(['u' => 'user'])
                ->leftJoin(['pr' => 'perfil'], 'pr.user_id = u.id')
                ->where([
                    'or',
                    ['like', 'u.username', $term],
                    ['like', 'pr.Frist_Name', $term],
                    ['like', 'pr.Last_Name', $term],
                    ['like', 'pr.Bio', $term],
                ])
                ->orderBy(['u.username' => SORT_ASC])
                ->limit(20)
                ->all();

            $posts = (new Query())
                ->select([
                    'p.id',
                    'p.titulo',
                    'p.conteudo',
                    'p.imagem',
                    'p.data_criacao',
                    'u.username',
                    'pr.Foto AS profile_photo',
                ])
                ->from(['p' => Post::tableName()])
                ->innerJoin(['u' => 'user'], 'u.id = p.user_id')
                ->leftJoin(['pr' => 'perfil'], 'pr.user_id = p.user_id')
                ->where([
                    'or',
                    ['like', 'p.titulo', $term],
                    ['like', 'p.conteudo', $term],
                    ['like', 'u.username', $term],
                    ['like', 'pr.Frist_Name', $term],
                    ['like', 'pr.Last_Name', $term],
                    ['like', 'pr.Bio', $term],
                ])
                ->orderBy(['p.data_criacao' => SORT_DESC, 'p.id' => SORT_DESC])
                ->limit(20)
                ->all();
        }

        return $this->render('@app/views/user/default/procurar', [
            'term' => $term,
            'users' => $users,
            'posts' => $posts,
        ]);
    }
}