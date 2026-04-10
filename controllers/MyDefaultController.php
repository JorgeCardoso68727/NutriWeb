<?php

namespace app\controllers;

use app\models\Post;
use app\models\BadgePedido;
use app\models\Seguidor;
use app\models\User;
use app\models\LoginForm;
use Yii;
use yii\db\Query;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\widgets\ActiveForm;

/**
 * Default controller for User module
 */
class MyDefaultController extends Controller
{
    /**
     * @var \amnah\yii2\user\Module
     * @inheritdoc
     */
    public $module;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'confirm', 'resend', 'logout', 'public-profile'],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                    [
                        'actions' => ['account', 'perfil', 'editar-perfil', 'resend-change', 'cancel', 'inicio', 'post-aberto', 'remove-post', 'feed', 'mensagens', 'gotinha', 'criarpost', 'toggle-follow', 'toggle-like', 'badge', 'badge-review'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['login', 'register', 'forgot', 'reset', 'login-email', 'login-callback'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                    'toggle-follow' => ['post'],
                    'toggle-like' => ['post'],
                    'remove-post' => ['post'],
                    'badge-review' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Display index - debug page, login page, or account page
     */
    public function actionIndex()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/user/inicio']);
        }

        if (defined('YII_DEBUG') && YII_DEBUG) {
            $actions = $this->module->getActions();
            return $this->render('index', ["actions" => $actions]);
        } elseif (Yii::$app->user->isGuest) {
            return $this->redirect(["/user/login"]);
        } else {
            return $this->redirect(["/user/account"]);
        }
    }

    /**
     * Display login page
     */
    public function actionLogin()
    {
        /** @var \app\models\LoginForm $model */
        $model = new LoginForm();

        // load post data and login
        $post = Yii::$app->request->post();
        if ($model->load($post) && $model->validate()) {
            $this->performLogin($model->getUser(), $model->rememberMe);
            return $this->redirect(['/user/inicio']);
        }

        return $this->render('login', compact("model"));
    }

    /**
     * Login/register via email
     */
    public function actionLoginEmail()
    {
        /** @var \amnah\yii2\user\models\forms\LoginEmailForm $loginEmailForm */
        //$loginEmailForm = $this->module->model("LoginEmailForm");
        $loginEmailForm = new $loginEmailForm();

        // load post data and validate
        $post = Yii::$app->request->post();
        if ($loginEmailForm->load($post) && $loginEmailForm->sendEmail()) {
            $user = $loginEmailForm->getUser();
            $message = $user ? "Login link sent" : "Registration link sent";
            $message .= " - Please check your email";
            Yii::$app->session->setFlash("Login-success", Yii::t("user", $message));
        }

        return $this->render("loginEmail", compact("loginEmailForm"));
    }

    /**
     * Login/register callback via email
     */
    public function actionLoginCallback($token)
    {
        /** @var \amnah\yii2\user\models\User $user */
        /** @var \amnah\yii2\user\models\Profile $profile */
        /** @var \amnah\yii2\user\models\Role $role */
        /** @var \amnah\yii2\user\models\UserToken $userToken */

        $user = $this->module->model("User");
        $profile = $this->module->model("Profile");
        $userToken = $this->module->model("UserToken");

        // check token and log user in directly
        $userToken = $userToken::findByToken($token, $userToken::TYPE_EMAIL_LOGIN);
        if ($userToken && $userToken->user) {
            $returnUrl = $this->performLogin($userToken->user, $userToken->data);
            $userToken->delete();
            return $this->redirect($returnUrl);
        }

        // load post data
        $post = Yii::$app->request->post();
        $userLoaded = $user->load($post);
        $profileLoaded = $profile->load($post);
        if ($userToken && ($userLoaded || $profileLoaded)) {

            // ensure that email is taken from the $userToken (and not from user input)
            $user->email = $userToken->data;

            // validate and register
            if ($user->validate() && $profile->validate()) {
                $role = $this->module->model("Role");
                $user->setRegisterAttributes($role::ROLE_USER, $user::STATUS_ACTIVE)->save();
                $profile->setUser($user->id)->save();

                // log user in and delete token
                $returnUrl = $this->performLogin($user);
                $userToken->delete();
                return $this->redirect($returnUrl);
            }
        }

        $user->email = $userToken ? $userToken->data : null;
        return $this->render("loginCallback", compact("user", "profile", "userToken"));
    }

    /**
     * Perform the login
     */
    protected function performLogin($user, $rememberMe = true)
    {
        // log user in
        $loginDuration = $rememberMe ? $this->module->loginDuration : 0;
        Yii::$app->user->login($user, $loginDuration);

        // check for a valid returnUrl (to prevent a weird login bug)
        //   https://github.com/amnah/yii2-user/issues/115
        $loginRedirect = $this->module->loginRedirect;
        $returnUrl = Yii::$app->user->getReturnUrl($loginRedirect);
        if (strpos($returnUrl, "user/login") !== false || strpos($returnUrl, "user/logout") !== false) {
            $returnUrl = null;
        }

        return $returnUrl;
    }

    /**
     * Log user out and redirect
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        // handle redirect
        $logoutRedirect = $this->module->logoutRedirect;
        if ($logoutRedirect) {
            return $this->redirect($logoutRedirect);
        }
        return $this->goHome();
    }

    /**
     * Display registration page
     */
    public function actionRegister()
    {
        /** @var \amnah\yii2\user\models\User $user */
        /** @var \app\models\Perfil $profile */
        /** @var \amnah\yii2\user\models\Role $role */

        // set up new user/profile objects
        $user = $this->module->model("User", ["scenario" => "register"]);
        $profile = $this->module->model("Profile");

        // load post data
        $post = Yii::$app->request->post();
        $userLoaded = $user->load($post);
        $profileLoaded = $profile->load($post);

        if ($userLoaded || $profileLoaded) {
            // validate for ajax request
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($user, $profile);
            }

            // validate for normal request
            if ($user->validate() && $profile->validate()) {
                $role = $this->module->model("Role");

                // save user first
                if ($user->setRegisterAttributes($role::ROLE_USER, $user::STATUS_ACTIVE)->save()) {
                    // bind profile to created user
                    $profile->setUser($user->id);

                    if ($profile->save()) {
                        // $this->afterRegister($user);  // TODO: ativar quando mailer estiver configurado

                        // Email confirmation is disabled for now, so log in immediately.
                        //Yii::$app->user->login($user, $this->module->loginDuration);

                        // set success flash
                        $successText = Yii::t(
                            "user",
                            "Successfully registered [ {displayName} ]",
                            ["displayName" => $user->getDisplayName()]
                        );
                        $guestText = "";
                        if (Yii::$app->user->isGuest) {
                            $guestText = Yii::t("user", " - Utilizador registado com sucesso");
                        }
                        Yii::$app->session->setFlash("Register-success", $successText . $guestText);
                        return $this->redirect(["/user/login"]);
                    } else {
                        Yii::$app->session->setFlash("Register-error", json_encode($profile->getErrors()));
                    }
                } else {
                    Yii::$app->session->setFlash("Register-error", json_encode($user->getErrors()));
                }
            }
        }

        return $this->render("register", compact("user", "profile"));
    }

    /**
     * Process data after registration
     * @param \amnah\yii2\user\models\User $user
     */
    protected function afterRegister($user)
    {
        /** @var \amnah\yii2\user\models\UserToken $userToken */
        $userToken = $this->module->model("UserToken");

        // determine userToken type to see if we need to send email
        $userTokenType = null;
        if ($user->status == $user::STATUS_INACTIVE) {
            $userTokenType = $userToken::TYPE_EMAIL_ACTIVATE;
        } elseif ($user->status == $user::STATUS_UNCONFIRMED_EMAIL) {
            $userTokenType = $userToken::TYPE_EMAIL_CHANGE;
        }

        // check if we have a userToken type to process, or just log user in directly
        if ($userTokenType) {
            $userToken = $userToken::generate($user->id, $userTokenType);
            if (!$numSent = $user->sendEmailConfirmation($userToken)) {

                // handle email error
                //Yii::$app->session->setFlash("Email-error", "Failed to send email");
            }
        } else {
            Yii::$app->user->login($user, $this->module->loginDuration);
        }
    }

    /**
     * Confirm email
     */
    public function actionConfirm($token)
    {
        /** @var \amnah\yii2\user\models\UserToken $userToken */
        /** @var \amnah\yii2\user\models\User $user */

        // search for userToken
        $success = false;
        $email = "";
        $userToken = $this->module->model("UserToken");
        $userToken = $userToken::findByToken($token, [$userToken::TYPE_EMAIL_ACTIVATE, $userToken::TYPE_EMAIL_CHANGE]);
        if ($userToken) {

            // find user and ensure that another user doesn't have that email
            //   for example, user registered another account before confirming change of email
            $user = $this->module->model("User");
            $user = $user::findOne($userToken->user_id);
            $newEmail = $userToken->data;
            if ($user->confirm($newEmail)) {
                $success = true;
            }

            // set email and delete token
            $email = $newEmail ?: $user->email;
            $userToken->delete();
        }

        return $this->render("confirm", compact("userToken", "success", "email"));
    }

    /**
     * Account
     */
    public function actionAccount()
    {
        /** @var \amnah\yii2\user\models\User $user */
        /** @var \amnah\yii2\user\models\UserToken $userToken */

        // set up user and load post data
        $user = Yii::$app->user->identity;
        $user->setScenario("account");
        $loadedPost = $user->load(Yii::$app->request->post());

        // validate for ajax request
        if ($loadedPost && Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($user);
        }

        // validate for normal request
        $userToken = $this->module->model("UserToken");
        if ($loadedPost && $user->validate()) {

            // check if user changed his email
            $newEmail = $user->checkEmailChange();
            if ($newEmail) {
                $userToken = $userToken::generate($user->id, $userToken::TYPE_EMAIL_CHANGE, $newEmail);
                if (!$numSent = $user->sendEmailConfirmation($userToken)) {

                    // handle email error
                    //Yii::$app->session->setFlash("Email-error", "Failed to send email");
                }
            }

            // save, set flash, and refresh page
            $user->save(false);
            Yii::$app->session->setFlash("Account-success", Yii::t("user", "Account updated"));
            return $this->refresh();
        } else {
            $userToken = $userToken::findByUser($user->id, $userToken::TYPE_EMAIL_CHANGE);
        }

        return $this->render("account", compact("user", "userToken"));
    }

    /**
     * Profile
     */
    public function actionPerfil()
    {
        $username = Yii::$app->user->identity->username;
        return $this->redirect('/' . $username);
    }

    public function actionPublicProfile($username = null)
    {
        if ($username === null && !Yii::$app->user->isGuest) {
            $username = Yii::$app->user->identity->username;
        }

        if ($username === null || trim((string) $username) === '') {
            throw new NotFoundHttpException('Perfil nao encontrado.');
        }

        $userClass = $this->module->model("User");
        $profileClass = $this->module->model("Profile");

        $viewUser = (new \yii\db\Query())
            ->from($userClass::tableName())
            ->where(['username' => $username])
            ->one();
        if ($viewUser === null) {
            throw new NotFoundHttpException('Perfil nao encontrado.');
        }
        $viewUser = (object) $viewUser;

        $profile = (new \yii\db\Query())
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
        $isOwnProfile = !Yii::$app->user->isGuest && (int) Yii::$app->user->id === $viewUserId;
        $posts = Post::find()
            ->where(['user_id' => $viewUserId])
            ->orderBy(['data_criacao' => SORT_DESC, 'id' => SORT_DESC])
            ->all();
        $publicationCount = (int) (new \yii\db\Query())
            ->from(Post::tableName())
            ->where(['user_id' => $viewUserId])
            ->count();
        $followersCount = Seguidor::countFollowers($viewUserId);
        $followingCount = Seguidor::countFollowing($viewUserId);
        $isFollowing = !Yii::$app->user->isGuest && Seguidor::isFollowing((int) Yii::$app->user->id, $viewUserId);
        $userTable = $userClass::tableName();

        $followersList = (new \yii\db\Query())
            ->select(['u.id', 'u.username'])
            ->from(['s' => Seguidor::tableName()])
            ->innerJoin(['u' => $userTable], 'u.id = s.seguidor_id')
            ->where(['s.seguido_id' => (int) $viewUser->id])
            ->orderBy(['u.username' => SORT_ASC])
            ->all();

        $followingList = (new \yii\db\Query())
            ->select(['u.id', 'u.username'])
            ->from(['s' => Seguidor::tableName()])
            ->innerJoin(['u' => $userTable], 'u.id = s.seguido_id')
            ->where(['s.seguidor_id' => (int) $viewUser->id])
            ->orderBy(['u.username' => SORT_ASC])
            ->all();

        return $this->render('perfil', [
            'viewUser' => $viewUser,
            'profile' => $profile,
            'isOwnProfile' => $isOwnProfile,
            'posts' => $posts,
            'publicationCount' => $publicationCount,
            'followersCount' => $followersCount,
            'followingCount' => $followingCount,
            'isFollowing' => $isFollowing,
            'followersList' => $followersList,
            'followingList' => $followingList,
        ]);
    }

    public function actionToggleFollow($username)
    {
        $userClass = $this->module->model("User");
        $targetUser = (new \yii\db\Query())
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
            $profile = $this->module->model("Profile");
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
                    return $this->render("editar-perfil", [
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

        return $this->render("editar-perfil", [
            'profile' => $profile,
            'user' => Yii::$app->user->identity,
        ]);
    }

    /**
     * Resend email confirmation
     */
    public function actionResend()
    {
        /** @var \amnah\yii2\user\models\forms\ResendForm $model */

        // load post data and send email
        $model = $this->module->model("ResendForm");
        if ($model->load(Yii::$app->request->post()) && $model->sendEmail()) {

            // set flash (which will show on the current page)
            Yii::$app->session->setFlash("Resend-success", Yii::t("user", "Confirmation email resent"));
            return $this->refresh();
        }

        return $this->render("resend", compact("model"));
    }

    /**
     * Resend email change confirmation
     */
    public function actionResendChange()
    {
        /** @var \amnah\yii2\user\models\User $user */
        /** @var \amnah\yii2\user\models\UserToken $userToken */

        // find userToken of type email change
        $user = Yii::$app->user->identity;
        $userToken = $this->module->model("UserToken");
        $userToken = $userToken::findByUser($user->id, $userToken::TYPE_EMAIL_CHANGE);
        if ($userToken) {

            // send email and set flash message
            $user->sendEmailConfirmation($userToken);
            Yii::$app->session->setFlash("Resend-success", Yii::t("user", "Confirmation email resent"));
        }

        return $this->redirect(["/user/account"]);
    }

    /**
     * Cancel email change
     */
    public function actionCancel()
    {
        /** @var \amnah\yii2\user\models\User $user */
        /** @var \amnah\yii2\user\models\UserToken $userToken */

        // find userToken of type email change
        $user = Yii::$app->user->identity;
        $userToken = $this->module->model("UserToken");
        $userToken = $userToken::findByUser($user->id, $userToken::TYPE_EMAIL_CHANGE);
        if ($userToken) {
            $userToken->delete();
            Yii::$app->session->setFlash("Cancel-success", Yii::t("user", "Email change cancelled"));
        }

        return $this->redirect(["/user/account"]);
    }

    /**
     * Forgot password
     */
    public function actionForgot()
    {
        /** @var \amnah\yii2\user\models\forms\ForgotForm $model */

        // load post data and send email
        $model = $this->module->model("ForgotForm");
        if ($model->load(Yii::$app->request->post()) && $model->sendForgotEmail()) {

            // set flash (which will show on the current page)
            Yii::$app->session->setFlash("Forgot-success", Yii::t("user", "Instructions to reset your password have been sent"));
            return $this->refresh();
        }

        return $this->render("forgot", compact("model"));
    }

    /**
     * Reset password
     */
    public function actionReset($token)
    {
        /** @var \amnah\yii2\user\models\User $user */
        /** @var \amnah\yii2\user\models\UserToken $userToken */

        // get user token and check expiration
        $userToken = $this->module->model("UserToken");
        $userToken = $userToken::findByToken($token, $userToken::TYPE_PASSWORD_RESET);
        if (!$userToken) {
            return $this->render('reset', ["invalidToken" => true]);
        }

        // get user and set "reset" scenario
        $success = false;
        $user = $this->module->model("User");
        $user = $user::findOne($userToken->user_id);
        $user->setScenario("reset");

        // load post data and reset user password
        if ($user->load(Yii::$app->request->post()) && $user->save()) {

            // delete userToken and set success = true
            $userToken->delete();
            $success = true;
        }

        return $this->render('reset', compact("user", "success"));
    }

    public function actionInicio()
    {
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
            ->where(['u.role_id' => 3])
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

        $nutritionists = (new Query())
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
            ->leftJoin(['pr' => 'perfil'], 'pr.user_id = u.id')
            ->where(['u.role_id' => 3])
            ->orderBy(['u.id' => SORT_DESC])
            ->limit(6)
            ->all();

        return $this->render('Inicio', [
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

        return $this->redirect(Yii::$app->request->referrer ?: ['inicio']);
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
        $isAdmin = $this->isCurrentUserAdmin();
        $canRemove = $isOwner || $isAdmin;
        $canReport = !$canRemove;

        return $this->render('PostAberto', [
            'post' => $post,
            'hasLiked' => $hasLiked,
            'likeCount' => $likeCount,
            'canRemove' => $canRemove,
            'canReport' => $canReport,
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
        $isAdmin = $this->isCurrentUserAdmin();

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
        return $this->redirect(['inicio']);
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

        return $this->render('feed', [
            'posts' => $posts,
            'likedPostIds' => $likedPostIds,
            'likeCountByPost' => $likeCountByPost,
        ]);
    }

    public function actionMensagens()
    {
        return $this->render('Mensagens');
    }

    public function actionGotinha()
    {
        return $this->render('gotinha');
    }

    public function actionBadge()
    {
        $userId = (int) Yii::$app->user->id;
        $isAdmin = $this->isCurrentUserAdmin();
        $profile = Yii::$app->user->identity->profile;
        $fullName = trim(($profile->Frist_Name ?? '') . ' ' . ($profile->Last_Name ?? ''));

        if (!$isAdmin && Yii::$app->request->isPost) {
            $uploadedPdf = UploadedFile::getInstanceByName('diplomaPdf');

            if ($uploadedPdf === null) {
                Yii::$app->session->setFlash('Badge-error', 'Seleciona um ficheiro PDF.');
                return $this->refresh();
            }

            $extension = strtolower((string) $uploadedPdf->extension);
            if ($extension !== 'pdf') {
                Yii::$app->session->setFlash('Badge-error', 'O ficheiro tem de ser PDF.');
                return $this->refresh();
            }

            $uploadDir = Yii::getAlias('@webroot/uploads/certificados');
            FileHelper::createDirectory($uploadDir);

            $safeBaseName = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($uploadedPdf->name, PATHINFO_FILENAME));
            $safeBaseName = $safeBaseName ?: 'certificado';
            $fileName = $userId . '_' . time() . '_' . $safeBaseName . '.pdf';
            $fullPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

            if (!$uploadedPdf->saveAs($fullPath)) {
                Yii::$app->session->setFlash('Badge-error', 'Nao foi possivel guardar o PDF.');
                return $this->refresh();
            }

            $pedido = new BadgePedido();
            $pedido->user_id = $userId;
            $pedido->diploma_pdf = 'uploads/certificados/' . $fileName;
            $pedido->estado = BadgePedido::ESTADO_PENDENTE;

            if ($pedido->save()) {
                Yii::$app->session->setFlash('Badge-success', 'Pedido de badge enviado para analise do administrador.');
            } else {
                Yii::$app->session->setFlash('Badge-error', 'Nao foi possivel guardar o pedido.');
            }

            return $this->refresh();
        }

        $lastPedido = (new \yii\db\Query())
            ->from(BadgePedido::tableName())
            ->where(['user_id' => $userId])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        $lastPedidoEstado = trim((string) ($lastPedido['estado'] ?? ''));
        $lastPedidoPdf = trim((string) ($lastPedido['diploma_pdf'] ?? ''));
        $hasLastPedido = $lastPedidoEstado !== '' || $lastPedidoPdf !== '';

        $pendingRequests = [];
        if ($isAdmin) {
            $pendingRequests = (new \yii\db\Query())
                ->select([
                    'b.id',
                    'b.user_id',
                    'b.diploma_pdf',
                    'b.estado',
                    'b.created_at',
                    'u.username',
                ])
                ->from(['b' => BadgePedido::tableName()])
                ->innerJoin(['u' => 'user'], 'u.id = b.user_id')
                ->where(['b.estado' => BadgePedido::ESTADO_PENDENTE])
                ->orderBy(['b.id' => SORT_DESC])
                ->all();
        }

        return $this->render('badge', [
            'isAdmin' => $isAdmin,
            'pendingRequests' => $pendingRequests,
            'fullName' => $fullName,
            'hasLastPedido' => $hasLastPedido,
            'lastPedidoEstado' => $lastPedidoEstado,
            'lastPedidoPdf' => $lastPedidoPdf,
        ]);
    }

    public function actionBadgeReview($id, $acao)
    {
        if (!$this->isCurrentUserAdmin()) {
            throw new NotFoundHttpException('Pagina nao encontrada.');
        }

        $pedido = (new \yii\db\Query())
            ->from(BadgePedido::tableName())
            ->where(['id' => (int) $id])
            ->one();
        if ($pedido === null) {
            throw new NotFoundHttpException('Pedido nao encontrado.');
        }

        if ($pedido['estado'] !== BadgePedido::ESTADO_PENDENTE) {
            Yii::$app->session->setFlash('Badge-error', 'Este pedido ja foi processado.');
            return $this->redirect(['/user/badge']);
        }

        if ($acao === 'aprovar') {
            $novoEstado = BadgePedido::ESTADO_APROVADO;
        } elseif ($acao === 'rejeitar') {
            $novoEstado = BadgePedido::ESTADO_REJEITADO;
        } else {
            Yii::$app->session->setFlash('Badge-error', 'Acao invalida.');
            return $this->redirect(['/user/badge']);
        }

        $pedidoModel = BadgePedido::findOne((int) $id);
        if ($pedidoModel === null) {
            throw new NotFoundHttpException('Pedido nao encontrado.');
        }

        $pedidoModel->estado = $novoEstado;
        $pedidoModel->admin_user_id = (int) Yii::$app->user->id;
        $pedidoModel->save(false);

        Yii::$app->session->setFlash('Badge-success', 'Pedido atualizado com sucesso.');
        return $this->redirect(['/user/badge']);
    }

    private function isCurrentUserAdmin()
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }

        $canAdmin = (new \yii\db\Query())
            ->select(['r.can_admin'])
            ->from(['u' => 'user'])
            ->innerJoin(['r' => 'role'], 'r.id = u.role_id')
            ->where(['u.id' => (int) Yii::$app->user->id])
            ->scalar();

        return (int) $canAdmin === 1;
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

        return $this->render('criarpost', [
            'post' => $post,
        ]);
    }
}
