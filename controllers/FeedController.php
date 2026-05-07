<?php

namespace app\controllers;

use app\helpers\RolePermissionHelper;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\web\Controller;

class FeedController extends Controller
{
    /**
     * @var \amnah\yii2\user\Module
     */
    public $module;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['inicio', 'feed', 'gotinha'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionInicio()
    {
        $postsQuery = (new Query())
            ->select([
                'p.id',
                'p.titulo',
                'p.imagem',
                'p.data_criacao',
                'p.CorPost',
                'p.user_id',
                'u.username',
                'pr.Foto AS profile_photo',
            ])
            ->from(['p' => 'post'])
            ->innerJoin(['u' => 'user'], 'u.id = p.user_id')
            ->leftJoin(['pr' => 'perfil'], 'pr.user_id = p.user_id');

        if (RolePermissionHelper::rolePermissionColumnExists('nutricionista')) {
            $postsQuery
                ->innerJoin(['r' => 'role'], 'r.id = u.role_id')
                ->where(['r.can_nutricionista' => 1]);
        } else {
            $postsQuery->where('1=0');
        }

        $posts = $postsQuery
            ->orderBy(['p.data_criacao' => SORT_DESC, 'p.id' => SORT_DESC])
            ->all();

        $postIds = array_map('intval', array_column($posts, 'id'));
        $likedPostIds = [];
        $likeCountByPost = [];

        if (!empty($postIds)) {
            $likeTotals = (new Query())
                ->select([
                    'id_post',
                    'total' => 'COUNT(*)',
                ])
                ->from(['l' => 'likes'])
                ->where(['l.id_post' => $postIds])
                ->groupBy(['l.id_post'])
                ->all();

            foreach ($likeTotals as $row) {
                $likeCountByPost[(int) $row['id_post']] = (int) $row['total'];
            }

            if (!Yii::$app->user->isGuest) {
                $userLikedPostIds = (new Query())
                    ->select(['l.id_post'])
                    ->from(['l' => 'likes'])
                    ->where([
                        'l.id_user' => (int) Yii::$app->user->id,
                        'l.id_post' => $postIds,
                    ])
                    ->column();

                $likedPostIds = array_fill_keys(array_map('intval', $userLikedPostIds), true);
            }
        }

        $nutritionistsQuery = (new Query())
            ->select([
                'u.id',
                'u.username',
                'pr.Frist_Name',
                'pr.Last_Name',
                'pr.Bio',
                'pr.Telefone',
                'pr.Foto AS profile_photo',
            ])
            ->from(['u' => 'user'])
            ->leftJoin(['pr' => 'perfil'], 'pr.user_id = u.id');

        if (RolePermissionHelper::rolePermissionColumnExists('nutricionista')) {
            $nutritionistsQuery
                ->innerJoin(['r' => 'role'], 'r.id = u.role_id')
                ->where(['r.can_nutricionista' => 1]);
        } else {
            $nutritionistsQuery->where('1=0');
        }

        $nutritionists = $nutritionistsQuery
            ->orderBy(['u.id' => SORT_DESC])
            ->limit(6)
            ->all();

        return $this->render('@app/views/user/default/Inicio', [
            'posts' => $posts,
            'likedPostIds' => $likedPostIds,
            'likeCountByPost' => $likeCountByPost,
            'nutritionists' => $nutritionists,
        ]);
    }

    public function actionFeed()
    {
        $userId = (int) Yii::$app->user->id;

        $posts = (new Query())
            ->select([
                'p.id',
                'p.titulo',
                'p.imagem',
                'p.data_criacao',
                'p.CorPost',
                'p.user_id',
                'u.username',
                'pr.Foto AS profile_photo',
            ])
            ->from(['p' => 'post'])
            ->innerJoin(['u' => 'user'], 'u.id = p.user_id')
            ->leftJoin(['pr' => 'perfil'], 'pr.user_id = p.user_id')
            ->orderBy(['p.data_criacao' => SORT_DESC, 'p.id' => SORT_DESC])
            ->all();

        $postIds = array_map('intval', array_column($posts, 'id'));
        $likedPostIds = [];
        $likeCountByPost = [];

        if (!empty($postIds)) {
            $likeTotals = (new Query())
                ->select([
                    'id_post',
                    'total' => 'COUNT(*)',
                ])
                ->from(['l' => 'likes'])
                ->where(['l.id_post' => $postIds])
                ->groupBy(['l.id_post'])
                ->all();

            foreach ($likeTotals as $row) {
                $likeCountByPost[(int) $row['id_post']] = (int) $row['total'];
            }

            $userLikedPostIds = (new Query())
                ->select(['l.id_post'])
                ->from(['l' => 'likes'])
                ->where([
                    'l.id_user' => $userId,
                    'l.id_post' => $postIds,
                ])
                ->column();

            $likedPostIds = array_fill_keys(array_map('intval', $userLikedPostIds), true);
        }

        return $this->render('@app/views/user/default/Feed', [
            'posts' => $posts,
            'likedPostIds' => $likedPostIds,
            'likeCountByPost' => $likeCountByPost,
        ]);
    }

    public function actionGotinha()
    {
        return $this->render('@app/views/user/default/gotinha');
    }
}
