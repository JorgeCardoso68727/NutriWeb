<?php

use app\assets\ForgotPasswordAsset;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var amnah\yii2\user\models\forms\ForgotForm $model
 * @var amnah\yii2\user\models\User|null $user
 * @var amnah\yii2\user\models\UserToken|null $userToken
 * @var bool $success
 * @var bool $invalidToken
 * @var string|null $token
 */

ForgotPasswordAsset::register($this);
$this->title = Yii::t('user', 'Forgot password');
$this->context->layout = false;

$this->beginPage();
?>

<?= Html::csrfMetaTags() ?>
<?php $this->head() ?>

<div class="body">
    <?php $this->beginBody() ?>

    <div class="login-container">
        <?= Html::img('@web/Img/Nutriweb Logo.png', ['class' => 'logo-img', 'alt' => 'Logo']) ?>
        <h1 class="brand-title">NUTRI<br>WEB</h1>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <p><?= Yii::t('user', 'Password has been reset') ?></p>
                <p><?= Html::a(Yii::t('user', 'Log in here'), ['/user/login']) ?></p>
            </div>
        <?php elseif (!empty($invalidToken)): ?>
            <div class="alert alert-danger">
                <p><?= Yii::t('user', 'Invalid token') ?></p>
            </div>
        <?php elseif (!empty($token) && $user): ?>
            <?php $form = ActiveForm::begin([
                'id' => 'reset-form',
                'options' => ['class' => 'form-container'],
                'fieldConfig' => [
                    'template' => "{input}\n{error}",
                    'errorOptions' => ['class' => 'invalid-feedback d-block', 'style' => 'text-align:left'],
                ],
            ]); ?>

            <div class="input-group password-group">
                <?= $form->field($user, 'newPassword', [
                    'template' => "{input}",
                    'options' => ['tag' => false],
                ])->passwordInput([
                    'id' => 'newPassword',
                    'class' => 'form-control',
                    'placeholder' => 'Nova password',
                ]) ?>
                <span class="input-group-text">
                    <i class="bi bi-eye" id="toggleNewPassword" style="cursor: pointer;"></i>
                </span>
            </div>
            <?= Html::error($user, 'newPassword', ['class' => 'invalid-feedback d-block mb-3']) ?>

            <div class="input-group password-group">
                <?= $form->field($user, 'newPasswordConfirm', [
                    'template' => "{input}",
                    'options' => ['tag' => false],
                ])->passwordInput([
                    'id' => 'newPasswordConfirm',
                    'class' => 'form-control',
                    'placeholder' => 'Confirmar password',
                ]) ?>
                <span class="input-group-text">
                    <i class="bi bi-eye" id="toggleConfirmPassword" style="cursor: pointer;"></i>
                </span>
            </div>
            <?= Html::error($user, 'newPasswordConfirm', ['class' => 'invalid-feedback d-block mb-4']) ?>

            <div class="d-grid">
                <?= Html::submitButton('Seguinte', ['class' => 'btn btn-Seguinte']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        <?php else: ?>
            <?php if ($flash = Yii::$app->session->getFlash('Forgot-success')): ?>
                <div class="alert alert-success">
                    <p><?= $flash ?></p>
                </div>
            <?php endif; ?>
            <p style="margin-bottom: 5px;"><b>Email de recuperação:</b></p>

            <?php $form = ActiveForm::begin([
                'id' => 'forgot-form',
                'options' => ['class' => 'form-container'],
                'fieldConfig' => [
                    'template' => "{input}\n{error}",
                    'errorOptions' => ['class' => 'invalid-feedback d-block', 'style' => 'text-align:left'],
                ],
            ]); ?>

            <div class="mb-3">
                <?= $form->field($model, 'email')->textInput([
                    'class' => 'form-control',
                    'placeholder' => 'Email',
                    'aria-label' => 'Email',
                ]) ?>
            </div>

            <div class="d-grid">
                <?= Html::submitButton('Seguinte', ['class' => 'btn btn-Seguinte']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        <?php endif; ?>
    </div>

    <?php
    $js = <<<JS
    const toggleNewPassword = document.querySelector('#toggleNewPassword');
    const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
    const newPassword = document.querySelector('#newPassword');
    const newPasswordConfirm = document.querySelector('#newPasswordConfirm');

    function toggleFieldVisibility(toggle, input) {
        if (!toggle || !input) {
            return;
        }

        toggle.addEventListener('click', function() {
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });
    }

    toggleFieldVisibility(toggleNewPassword, newPassword);
    toggleFieldVisibility(toggleConfirmPassword, newPasswordConfirm);
JS;
    $this->registerJs($js);
    ?>

    <?php $this->endBody() ?>
</div>
<?php $this->endPage() ?>