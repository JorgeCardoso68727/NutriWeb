<?php

namespace app\models\forms;

use Yii;

class ForgotForm extends \amnah\yii2\user\models\forms\ForgotForm
{
    /**
     * Send forgot password email with customized subject.
     * @return bool
     */
    public function sendForgotEmail()
    {
        if ($this->validate()) {
            $user = $this->getUser();

            $expireTime = $this->module->resetExpireTime;
            $expireTime = $expireTime ? gmdate('Y-m-d H:i:s', strtotime($expireTime)) : null;

            $userToken = $this->module->model('UserToken');
            $userToken = $userToken::generate($user->id, $userToken::TYPE_PASSWORD_RESET, null, $expireTime);

            $mailer = Yii::$app->mailer;
            $oldViewPath = $mailer->viewPath;
            $mailer->viewPath = $this->module->emailViewPath;

            $subject = 'NutriWeb - Reposicao de password';
            $result = $mailer->compose('forgotPassword', compact('subject', 'user', 'userToken'))
                ->setTo($user->email)
                ->setSubject($subject)
                ->send();

            $mailer->viewPath = $oldViewPath;
            return $result;
        }

        return false;
    }
}
