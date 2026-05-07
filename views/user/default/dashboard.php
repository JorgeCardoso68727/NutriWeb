<?php

/** @var yii\web\View $this */
/** @var int $userReportsCount */
/** @var int $contentReportsCount */
/** @var int $eventReportsCount */
/** @var int $badgesNutricionistaCount */
/** @var int $badgesInstituicaoCount */

use app\assets\DashboardAsset;
use yii\helpers\Html;
use yii\helpers\Url;

DashboardAsset::register($this);

$this->title = 'Dashboard';
?>

<div class="dashboard-container">
    <h1><?= $this->title ?></h1>

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

    <div class="row g-4">
        <div class="col-lg-3 col-md-6">
            <div class="caixa reports">
                <i class="bi fs-1 bi-person-fill-gear"></i>
                <h3>Reports de Contas</h3>
                <p class="grafico"><?= $userReportsCount ?></p>
                <a href="<?= Url::to(['/reports/reports-accounts']) ?>" class="btn btn-primary">Ver Detalhes</a>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="caixa reports">
                <i class="bi fs-1 bi-person-fill-gear"></i>
                <h3>Reports de Posts</h3>
                <p class="grafico"><?= $contentReportsCount ?></p>
                <a href="<?= Url::to(['/reports/reports-content']) ?>" class="btn btn-primary">Ver Detalhes</a>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="caixa reports">
                <i class="bi fs-1 bi-calendar-x"></i>
                <h3>Reports de Eventos</h3>
                <p class="grafico"><?= (int) $eventReportsCount ?></p>
                <a href="<?= Url::to(['/reports/reports-events']) ?>" class="btn btn-primary">Ver Detalhes</a>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="caixa reports">
                <i class="bi fs-1 bi-patch-check"></i>
                <h3>Badges Nutricionista</h3>
                <p class="grafico"><?= $badgesNutricionistaCount ?></p>
                <a href="<?= Url::to(['/badge']) ?>" class="btn btn-primary">Ver Detalhes</a>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="caixa reports">
                <i class="bi fs-1 bi-patch-check"></i>
                <h3>Badges Instituição</h3>
                <p class="grafico"><?= $badgesInstituicaoCount ?></p>
                <a href="<?= Url::to(['/badge', 'tipo' => 'instituicao']) ?>" class="btn btn-primary">Ver Detalhes</a>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="caixa reports">
                <i class="bi fs-1 bi-person-plus-fill"></i>
                <h3>Criar Admin</h3>
                <p class="grafico"></p>
                <a href="<?= Url::to(['/reports/admin-create']) ?>" class="btn btn-primary">Abrir Ferramenta</a>
            </div>
        </div>
    </div>
</div>