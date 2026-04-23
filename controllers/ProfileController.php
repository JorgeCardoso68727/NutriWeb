<?php

namespace app\controllers;

use app\helpers\RolePermissionHelper;
use app\models\PlanoNutricional;
use app\models\Post;
use app\models\Seguidor;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class ProfileController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['public-profile'],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                    [
                        'actions' => ['perfil', 'editar-perfil', 'toggle-follow'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'toggle-follow' => ['post'],
                ],
            ],
        ];
    }

    //Redireciona para o perfil do utilizador logado
    public function actionPerfil()
    {
        $username = Yii::$app->user->identity->username;
        return $this->redirect('/' . $username);
    }

    //Redireciona para o perfil publico do utilizador.
    public function actionPublicProfile($username = null)
    {
        if ($username === null && !Yii::$app->user->isGuest) {
            $username = Yii::$app->user->identity->username;
        }

        if ($username === null || trim((string) $username) === '') {
            throw new NotFoundHttpException('Perfil nao encontrado.');
        }

        //Busca o utilizador e o perfil associado com base no username fornecido
        $userModule = Yii::$app->getModule('user');
        $userClass = $userModule->model("User");
        $profileClass = $userModule->model("Profile");

        $viewUser = (new Query())
            ->from($userClass::tableName())
            ->where(['username' => $username])
            ->one();
        if ($viewUser === null) {
            throw new NotFoundHttpException('Perfil nao encontrado.');
        }
        $viewUser = (object) $viewUser;

        if (!isset($viewUser->id)) {
            throw new NotFoundHttpException('Perfil nao encontrado.');
        }

        $profile = (new Query())
            ->from($profileClass::tableName())
            ->where(['user_id' => $viewUser->id])
            ->one();
        if ($profile === null) {
            $profile = new $profileClass();
            $profile->setAttributes([
                'Frist_Name' => '',
                'Last_Name' => '',
                'Bio' => '',
                'Foto' => '',
            ], false);
        } else {
            $profile = (object) $profile;
        }

        $viewUserId = (int) $viewUser->id;
        if ($viewUserId <= 0) {
            throw new NotFoundHttpException('Perfil nao encontrado.');
        }
        $isOwnProfile = !Yii::$app->user->isGuest && (int) Yii::$app->user->id === $viewUserId;
        $isAdminViewer = RolePermissionHelper::isCurrentUserAdmin();
        $isViewedUserAdmin = RolePermissionHelper::isUserAdmin($viewUserId);
        $isReviewMode = (int) Yii::$app->request->get('rever', 0) === 1;
        $posts = Post::find()
            ->where(['user_id' => $viewUserId])
            ->orderBy(['data_criacao' => SORT_DESC, 'id' => SORT_DESC])
            ->all();
        $publicationCount = (int) (new Query())
            ->from(Post::tableName())
            ->where(['user_id' => $viewUserId])
            ->count();
        $followersCount = Seguidor::countFollowers($viewUserId);
        $followingCount = Seguidor::countFollowing($viewUserId);
        $isFollowing = !Yii::$app->user->isGuest && Seguidor::isFollowing((int) Yii::$app->user->id, $viewUserId);
        $isNutritionistProfile = RolePermissionHelper::isUserNutritionist($viewUserId);
        $plans = PlanoNutricional::find()
            ->where(['user_id' => $viewUserId])
            ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])
            ->all();
        $userTable = $userClass::tableName();

        $followersList = (new Query())
            ->select(['u.id', 'u.username'])
            ->from(['s' => Seguidor::tableName()])
            ->innerJoin(['u' => $userTable], 'u.id = s.seguidor_id')
            ->where(['s.seguido_id' => (int) $viewUser->id])
            ->orderBy(['u.username' => SORT_ASC])
            ->all();

        $followingList = (new Query())
            ->select(['u.id', 'u.username'])
            ->from(['s' => Seguidor::tableName()])
            ->innerJoin(['u' => $userTable], 'u.id = s.seguido_id')
            ->where(['s.seguidor_id' => (int) $viewUser->id])
            ->orderBy(['u.username' => SORT_ASC])
            ->all();

        $username = (string) ($viewUser->username ?? 'utilizador');
        $fullName = trim((string) ($profile->Frist_Name ?? '') . ' ' . (string) ($profile->Last_Name ?? ''));
        $displayName = $fullName !== '' ? $fullName : $username;
        $bio = trim((string) ($profile->Bio ?? ''));
        $profilePhotoPath = trim((string) ($profile->Foto ?? ''));
        $avatarPath = $profilePhotoPath !== '' ? '@web/' . ltrim($profilePhotoPath, '/') : '@web/Img/default.jpeg';

        $this->view->title = 'Nutriweb - ' . $username;

        return $this->render('@app/views/user/default/perfil', [
            'viewUser' => $viewUser,
            'profile' => $profile,
            'isOwnProfile' => $isOwnProfile,
            'isAdminViewer' => $isAdminViewer,
            'isViewedUserAdmin' => $isViewedUserAdmin,
            'isReviewMode' => $isReviewMode,
            'posts' => $posts,
            'publicationCount' => $publicationCount,
            'followersCount' => $followersCount,
            'followingCount' => $followingCount,
            'isFollowing' => $isFollowing,
            'isNutritionistProfile' => $isNutritionistProfile,
            'followersList' => $followersList,
            'followingList' => $followingList,
            'plans' => $plans,
            'username' => $username,
            'displayName' => $displayName,
            'bio' => $bio,
            'avatarPath' => $avatarPath,
        ]);
    }

    public function actionToggleFollow($username)
    {
        $userModule = Yii::$app->getModule('user');
        $userClass = $userModule->model("User");
        $targetUser = (new Query())
            ->from($userClass::tableName())
            ->where(['username' => $username])
            ->one();

        if ($targetUser === null) {
            throw new NotFoundHttpException('Perfil nao encontrado.');
        }

        $targetUser = (object) $targetUser;

        $seguidorId = (int) Yii::$app->user->id;
        $seguidoId = (int) $targetUser->id;

        if ($seguidorId !== $seguidoId) {
            if (Seguidor::isFollowing($seguidorId, $seguidoId)) {
                Seguidor::unfollow($seguidorId, $seguidoId);
            } else {
                Seguidor::follow($seguidorId, $seguidoId);
            }
        }

        return $this->redirect('/' . $targetUser->username);
    }

    public function actionEditarPerfil()
    {
        $profile = Yii::$app->user->identity->profile;

        if (!$profile) {
            $userModule = Yii::$app->getModule('user');
            $profile = $userModule->model("Profile");
            $profile->setUser(Yii::$app->user->id);
        }

        $oldPhotoPath = $profile->Foto;

        if ($profile->load(Yii::$app->request->post()) && $profile->validate()) {
            $uploadedPhoto = UploadedFile::getInstanceByName('profilePhoto');

            if ($uploadedPhoto) {
                $uploadDir = Yii::getAlias('@webroot/uploads/profile');
                FileHelper::createDirectory($uploadDir);

                $safeBaseName = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($uploadedPhoto->name, PATHINFO_FILENAME));
                $safeBaseName = $safeBaseName ?: 'foto';
                $fileName = Yii::$app->user->id . '_' . time() . '_' . $safeBaseName . '.' . $uploadedPhoto->extension;
                $fullPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

                if ($uploadedPhoto->saveAs($fullPath)) {
                    $profile->Foto = 'uploads/profile/' . $fileName;
                } else {
                    $profile->Foto = $oldPhotoPath;
                    Yii::$app->session->setFlash('Profile-error', 'Nao foi possivel guardar a foto.');
                    return $this->render('@app/views/user/default/editar-perfil', [
                        'profile' => $profile,
                        'user' => Yii::$app->user->identity,
                    ]);
                }
            } else {
                $profile->Foto = $oldPhotoPath;
            }

            $updated = \app\models\Perfil::updateAll([
                'Frist_Name' => $profile->Frist_Name,
                'Last_Name' => $profile->Last_Name,
                'Bio' => $profile->Bio,
                'Foto' => $profile->Foto,
            ], ['id' => $profile->id]);

            if ($updated) {
                Yii::$app->session->setFlash("Profile-success", Yii::t("user", "Perfil atualizado"));
                return $this->redirect('/' . Yii::$app->user->identity->username);
            }
        }

        return $this->render('@app/views/user/default/editar-perfil', [
            'profile' => $profile,
            'user' => Yii::$app->user->identity,
        ]);
    }
}
