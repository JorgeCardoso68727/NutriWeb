<?php

/** @var yii\web\View $this */
/** @var amnah\yii2\user\models\User $user */
/** @var app\models\Perfil $profile */

use app\assets\DashboardAsset;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

DashboardAsset::register($this);
$this->title = 'Criar conta admin';
?>

<div class="dashboard-container">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <h1 class="mb-0"><?= Html::encode($this->title) ?></h1>
        <a class="btn btn-outline-secondary" href="<?= Url::to(['/reports/dashboard']) ?>">
            Voltar ao dashboard
        </a>
    </div>

    <?php if (Yii::$app->session->hasFlash('AdminCreate-success')): ?>
        <div class="alert alert-success mb-4 js-auto-dismiss-alert" data-auto-dismiss="1"><?= Html::encode(Yii::$app->session->getFlash('AdminCreate-success')) ?></div>
    <?php endif; ?>
    <?php if (Yii::$app->session->hasFlash('AdminCreate-error')): ?>
        <div class="alert alert-danger mb-4 js-auto-dismiss-alert" data-auto-dismiss="1"><?= Html::encode(Yii::$app->session->getFlash('AdminCreate-error')) ?></div>
    <?php endif; ?>

    <?php $this->registerJs(<<<JS
(function () {
    document.querySelectorAll('.js-auto-dismiss-alert[data-auto-dismiss="1"]').forEach(function (alertNode) {
        window.setTimeout(function () {
            if (window.bootstrap && window.bootstrap.Alert) {
                window.bootstrap.Alert.getOrCreateInstance(alertNode).close();
                return;
            }

            alertNode.remove();
        }, 2000);
    });
})();
JS); ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-md-5">
            <?php $form = ActiveForm::begin([
                'id' => 'admin-create-form',
                'enableAjaxValidation' => true,
                'validateOnBlur' => true,
            ]); ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <?= $form->field($profile, 'Frist_Name')->textInput(['placeholder' => 'Primeiro nome']) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($profile, 'Last_Name')->textInput(['placeholder' => 'Ultimo nome']) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($profile, 'Telefone')->input('number', ['placeholder' => 'Telefone']) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($user, 'username')->textInput(['placeholder' => 'Username da conta admin']) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($user, 'email')->input('email', ['placeholder' => 'Email da conta admin']) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($user, 'newPassword')->passwordInput(['placeholder' => 'Palavra-passe temporária']) ?>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <?= Html::submitButton('Criar conta admin', ['class' => 'btn btn-primary']) ?>
                <a href="<?= Url::to(['/reports/dashboard']) ?>" class="btn btn-outline-secondary">Cancelar</a>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>