<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm; // Usamos bootstrap5 para manter a compatibilidade
use app\assets\RegisterAsset;

RegisterAsset::register($this);
$this->title = 'Nutriweb - Registrar';
$this->context->layout = false; // Remove o menu padrão do Yii

$this->beginPage();
?>

<?= Html::csrfMetaTags() ?>
<?php $this->head() ?>

<div class="body-Login-register">
    <?php $this->beginBody() ?>

    <div class="container register-container">
        <div class="row align-items-center">

            <div class="col-md-5 logo-area">
                <?= Html::img('@web/Img/Nutriweb Logo.png', ['class' => 'logo-img-g', 'alt' => 'Logo']) ?>
                <h1 class="brand-title">NUTRI<br>WEB</h1>
            </div>

            <div class="col-md-7">
                <?php if ($flash = Yii::$app->session->getFlash("Register-success")): ?>
                    <div class="alert alert-success"><?= $flash ?></div>
                <?php else: ?>

                    <?php $form = ActiveForm::begin([
                        'id' => 'register-form',
                        'fieldConfig' => [
                            'template' => "{input}\n{error}",
                            'inputOptions' => ['class' => 'form-control'],
                            'errorOptions' => ['class' => 'invalid-feedback d-block'],
                        ],
                    ]); ?>
                    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken()) ?>
                    <div class="row g-2">
                        <div class="col-6">
                            <?= $form->field($profile, 'Frist_Name')->textInput(['placeholder' => 'Primeiro nome']) ?>
                        </div>
                        <div class="col-6">
                            <?= $form->field($profile, 'Last_Name')->textInput(['placeholder' => 'Último nome']) ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <?= $form->field($user, 'username')->textInput(['placeholder' => 'Nome de utilizador']) ?>
                    </div>

                    <div class="mb-3">
                        <?= $form->field($profile, 'Telefone')->textInput(['type' => 'tel', 'placeholder' => 'Número Telefone']) ?>
                    </div>

                    <div class="mb-3">
                        <?= $form->field($user, 'email')->textInput(['type' => 'email', 'placeholder' => 'Email']) ?>
                    </div>

                    <div class="input-group mb-3 password-group">
                        <?= $form->field($user, 'newPassword', [
                            'options' => ['tag' => false],
                            'template' => "{input}"
                        ])->passwordInput(['id' => 'pass1', 'placeholder' => 'Password']) ?>
                        <span class="input-group-text"><i class="bi bi-eye" id="icon1" style="cursor:pointer"></i></span>
                        <?= Html::error($user, 'newPassword', ['class' => 'invalid-feedback d-block']) ?>
                    </div>

                    <div class="input-group mb-3 password-group">
                        <?= $form->field($user, 'newPasswordConfirm', [
                            'options' => ['tag' => false],
                            'template' => "{input}"
                        ])->passwordInput(['id' => 'pass2', 'placeholder' => 'Repita a password']) ?>
                        <span class="input-group-text"><i class="bi bi-eye" id="icon2" style="cursor:pointer"></i></span>
                        <?= Html::error($user, 'newPasswordConfirm', ['class' => 'invalid-feedback d-block']) ?>
                    </div>

                    <?= Html::submitButton('Registre-se', ['class' => 'btn btn-register']) ?>

                    <div class="login-link">
                        Já tem conta? <?= Html::a('Faça Login!', ['/user/login']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php
    // JS para os dois olhos das passwords
    $js = <<<JS
    function setupToggle(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        icon.addEventListener('click', function() {
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });
    }
    setupToggle('pass1', 'icon1');
    setupToggle('pass2', 'icon2');
JS;
    $this->registerJs($js);
    ?>

    <?php $this->endBody() ?>
</div>
<?php $this->endPage() ?>