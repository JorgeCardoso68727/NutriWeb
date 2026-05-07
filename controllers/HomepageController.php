<?php

namespace app\controllers;

use app\helpers\RolePermissionHelper;
use app\models\Post;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class HomepageController extends Controller
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
                        'actions' => ['post-aberto'],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                    [
                        'actions' => ['inicio', 'feed', 'mensagens', 'gotinha', 'criarpost', 'toggle-like', 'remove-post'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'toggle-like' => ['post'],
                    'remove-post' => ['post'],
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

    public function actionToggleLike($postId)
    {
        $postId = (int) $postId;
        $userId = (int) Yii::$app->user->id;
        $isAjaxRequest = Yii::$app->request->isAjax;

        $postExists = (new Query())
            ->from(['p' => 'post'])
            ->where(['p.id' => $postId])
            ->exists();

        if (!$postExists) {
            if ($isAjaxRequest) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                Yii::$app->response->statusCode = 404;
                return [
                    'success' => false,
                    'message' => 'Post nao encontrado.',
                ];
            }

            throw new NotFoundHttpException('Post nao encontrado.');
        }

        $alreadyLiked = (new Query())
            ->from(['l' => 'likes'])
            ->where([
                'l.id_post' => $postId,
                'l.id_user' => $userId,
            ])
            ->exists();

        if ($alreadyLiked) {
            Yii::$app->db->createCommand()->delete('{{%likes}}', [
                'id_post' => $postId,
                'id_user' => $userId,
            ])->execute();
            $liked = false;
        } else {
            Yii::$app->db->createCommand()->insert('{{%likes}}', [
                'id_post' => $postId,
                'id_user' => $userId,
            ])->execute();
            $liked = true;
        }

        $likeCount = (int) (new Query())
            ->from(['l' => 'likes'])
            ->where(['l.id_post' => $postId])
            ->count();

        if ($isAjaxRequest) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'success' => true,
                'liked' => $liked,
                'likeCount' => $likeCount,
            ];
        }

        return $this->redirect(Yii::$app->request->referrer ?: ['/inicio']);
    }

    public function actionPostAberto($id)
    {
        $postId = (int) $id;

        $post = (new Query())
            ->select([
                'p.id',
                'p.titulo',
                'p.conteudo',
                'p.imagem',
                'p.data_criacao',
                'p.CorPost',
                'p.user_id',
                'u.username',
                'pr.Foto AS profile_photo',
            ])
            ->from(['p' => 'post'])
            ->leftJoin(['u' => 'user'], 'u.id = p.user_id')
            ->leftJoin(['pr' => 'perfil'], 'pr.user_id = p.user_id')
            ->where(['p.id' => $postId])
            ->one();

        if (!$post) {
            throw new NotFoundHttpException('Post nao encontrado.');
        }

        $hasLiked = false;
        if (!Yii::$app->user->isGuest) {
            $hasLiked = (new Query())
                ->from(['l' => 'likes'])
                ->where([
                    'l.id_post' => $postId,
                    'l.id_user' => (int) Yii::$app->user->id,
                ])
                ->exists();
        }

        $likeCount = (int) (new Query())
            ->from(['l' => 'likes'])
            ->where(['l.id_post' => $postId])
            ->count();

        $currentUserId = (int) Yii::$app->user->id;
        $isOwner = $currentUserId > 0 && $currentUserId === (int) $post['user_id'];
        $isAdmin = RolePermissionHelper::isCurrentUserAdmin();
        $isReviewMode = (int) Yii::$app->request->get('rever', 0) === 1;
        $canRemove = $isOwner || $isAdmin;
        $canReport = !$canRemove;

        return $this->render('@app/views/user/default/PostAberto', [
            'post' => $post,
            'hasLiked' => $hasLiked,
            'likeCount' => $likeCount,
            'canRemove' => $canRemove,
            'canReport' => $canReport,
            'isAdminViewer' => $isAdmin,
            'isReviewMode' => $isReviewMode,
        ]);
    }

    public function actionRemovePost($id)
    {
        $postId = (int) $id;
        $post = Post::findOne($postId);

        if ($post === null) {
            throw new NotFoundHttpException('Post nao encontrado.');
        }

        $currentUserId = (int) Yii::$app->user->id;
        $isOwner = $currentUserId > 0 && $currentUserId === (int) $post->user_id;
        $isAdmin = RolePermissionHelper::isCurrentUserAdmin();

        if (!$isOwner && !$isAdmin) {
            throw new ForbiddenHttpException('Nao tens permissao para remover este post.');
        }

        $imagePath = trim((string) $post->imagem);
        $fullImagePath = $imagePath !== '' ? Yii::getAlias('@webroot/' . ltrim($imagePath, '/')) : '';

        Yii::$app->db->createCommand()->delete('{{%likes}}', ['id_post' => $postId])->execute();
        $post->delete();

        if ($fullImagePath !== '' && is_file($fullImagePath)) {
            @unlink($fullImagePath);
        }

        Yii::$app->session->setFlash('Post-success', 'Post removido com sucesso.');
        return $this->redirect(['/inicio']);
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

        return $this->render('@app/views/user/default/feed', [
            'posts' => $posts,
            'likedPostIds' => $likedPostIds,
            'likeCountByPost' => $likeCountByPost,
        ]);
    }

    public function actionMensagens()
    {
        return $this->render('@app/views/user/default/mensagens');
    }

    public function actionGotinha()
    {
        return $this->render('@app/views/user/default/gotinha');
    }

    public function actionCriarpost()
    {
        $post = new Post();

        if ($post->load(Yii::$app->request->post())) {
            $post->user_id = (int) Yii::$app->user->id;
            $post->data_criacao = date('Y-m-d H:i:s');

            if (empty($post->titulo) && !empty($post->conteudo)) {
                $post->titulo = mb_substr(trim($post->conteudo), 0, 80);
            }

            $uploadedImage = UploadedFile::getInstance($post, 'imagem');
            if ($uploadedImage !== null) {
                $uploadDir = Yii::getAlias('@webroot/uploads/posts');
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0775, true);
                }

                $fileName = uniqid('post_', true) . '.' . $uploadedImage->extension;
                $filePath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

                if ($uploadedImage->saveAs($filePath)) {
                    $post->imagem = 'uploads/posts/' . $fileName;
                } else {
                    $post->addError('imagem', 'Nao foi possivel guardar a imagem.');
                }
            }

            if (!$post->hasErrors() && $post->save()) {
                Yii::$app->session->setFlash('Post-success', 'Post publicado com sucesso.');
                return $this->refresh();
            }

            Yii::$app->session->setFlash('Post-error', implode(' | ', $post->getFirstErrors()));
        }

        return $this->render('@app/views/user/default/criarpost', [
            'post' => $post,
        ]);
    }
}
