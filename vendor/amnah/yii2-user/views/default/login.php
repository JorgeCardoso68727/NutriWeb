<?php

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;
use app\assets\LoginAsset;

LoginAsset::register($this);
$this->title = 'My Yii Application';
$this->context->layout = false;

$this->beginPage();
?>

<?= Html::csrfMetaTags() ?>
<?php $this->head() ?>


<div class="body-Login-register">
<?php $this->beginBody() ?>

<div class="login-container">
    
    <img class="logo-img" src="<?= Url::to('@web/Img/Nutriweb Logo.png') ?>" alt="Logo">

    <h1 class="brand-title">
        NUTRI<br>WEB
    </h1>

    <?php $form = ActiveForm::begin([
        'id' => 'login-form',
        'options' => ['class' => 'form-container'], // Adiciona classe se precisares no CSS
        'fieldConfig' => [
            'template' => "{input}\n{error}", 
            'errorOptions' => ['class' => 'invalid-feedback d-block', 'style' => 'text-align:left'],
        ],
    ]); ?>

        <div class="mb-3">
            <?= $form->field($model, 'email')->textInput([
                'class' => 'form-control',
                'placeholder' => 'Email',   
                'aria-label' => 'Email'
            ]) ?>
        </div>

        <div class="input-group mb-4 password-group">
            <?= $form->field($model, 'password', [
                'template' => "{input}", // Remove labels e erros daqui para não quebrar o grupo
                'options' => ['tag' => false], // Remove a div envolvente do Yii
            ])->passwordInput([
                'id' => 'passwordInput',
                'class' => 'form-control',
                'placeholder' => 'Password',
            ]) ?>
            
            <span class="input-group-text ">
                <i class="bi bi-eye" id="toggleIcon" style="cursor: pointer;"></i>
            </span>
    
            <?= Html::error($model, 'password', ['class' => 'invalid-feedback d-block']) ?>
        </div>

        <div class="resetPassword-link">
            <a href="<?= Url::to(['site/request-password-reset']) ?>">Repor Password</a>
        </div>

        <div class="d-grid">
            <?= Html::submitButton('Login', ['class' => 'btn btn-login', 'name' => 'login-button']) ?>
        </div>

        <div class="register-link">
            Não tem conta? <a href="<?= Url::to(['/user/register']) ?>">Crie uma!</a>
        </div>

    <?php ActiveForm::end(); ?>
</div>

<?php 
// Script para o olho da password funcionar
$js = <<<JS
    const toggleIcon = document.querySelector('#toggleIcon');
    const passwordInput = document.querySelector('#passwordInput');
    toggleIcon.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.classList.toggle('bi-eye');
        this.classList.toggle('bi-eye-slash');
    });
JS;
$this->registerJs($js);
?>

<?php $this->endBody() ?>
</div>
</html>
<?php $this->endPage() ?>







<?php
