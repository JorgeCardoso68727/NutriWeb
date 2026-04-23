<?php

namespace app\controllers;

use app\models\Post;
use app\models\User;
use yii\models\Perfil;
use app\models\LoginForm;
use Yii;
use yii\db\Query;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
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
                        'actions' => ['account', 'resend-change', 'cancel'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['login', 'register', 'forgot', 'reset', 'login-email', 'login-callback'],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
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
            return $this->redirect(['/inicio']);
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
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/inicio']);
        }

        /** @var \app\models\LoginForm $model */
        $model = new LoginForm();

        // load post data and login
        $post = Yii::$app->request->post();
        if ($model->load($post) && $model->validate()) {
            $this->performLogin($model->getUser(), $model->rememberMe);
            return $this->redirect(['/inicio']);
        }

        return $this->render('login', compact("model"));
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


//Nao usados por agora;

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
     * Display registration page
     */
    public function actionRegister()
    {
        /** @var \amnah\yii2\user\models\User $user */
        /** @var \app\models\Perfil $profile */
        /** @var \amnah\yii2\user\models\Role $role */

        //o que estas linhas fazem? --- IGNORE ---
        // 1. Cria uma instância do modelo User com o cenário "register" para validação específica de registro.
        // 2. Cria uma instância do modelo Perfil para armazenar informações adicionais do

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
    public function actionForgot($token = null)
    {
        /** @var \amnah\yii2\user\models\forms\ForgotForm $model */
        /** @var \amnah\yii2\user\models\User $user */
        /** @var \amnah\yii2\user\models\UserToken $userToken */

        $model = $this->module->model("ForgotForm");
        $user = null;
        $userToken = null;
        $success = false;
        $invalidToken = false;

        if ($token) {
            $userTokenClass = $this->module->model("UserToken");
            $userToken = $userTokenClass::findByToken($token, $userTokenClass::TYPE_PASSWORD_RESET);
            if ($userToken) {
                $userClass = $this->module->model("User");
                $user = $userClass::findOne($userToken->user_id);
                if ($user) {
                    $user->setScenario("reset");
                } else {
                    $invalidToken = true;
                }
            } else {
                $invalidToken = true;
            }
        }

        if (Yii::$app->request->isPost) {
            if ($token && $user && $user->load(Yii::$app->request->post()) && $user->save()) {
                $userToken->delete();
                Yii::$app->session->setFlash("success", Yii::t("user", "Password has been reset"));
                return $this->redirect(["/user/login"]);
            } elseif (!$token && $model->load(Yii::$app->request->post()) && $model->sendForgotEmail()) {

                // set flash (which will show on the current page)
                Yii::$app->session->setFlash("Forgot-success", Yii::t("user", "As instruções para redefinir a senha foram enviadas para o seu email"));
                return $this->refresh();
            }
        }

        return $this->render("forgot", compact("model", "user", "userToken", "success", "invalidToken", "token"));
    }

    /**
     * Reset password
     */
    public function actionReset($token)
    {
        return $this->redirect(["/user/default/forgot", "token" => $token]);
    }
}
