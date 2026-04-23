<?php

/** @var yii\web\View $this */
/** @var int $userReportsCount */
/** @var int $contentReportsCount */
/** @var int $badgesCount */

use app\assets\DashboardAsset;
use yii\helpers\Url;

DashboardAsset::register($this);

$this->title = 'Dashboard';
?>

<div class="dashboard-container">
    <h1><?= $this->title ?></h1>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="caixa reports">
                <i class="bi fs-1 bi-person-fill-gear"></i>
                <h3>Reports de Contas</h3>
                <p class="grafico"><?= $userReportsCount ?></p>
                <a href="<?= Url::to(['/reports/reports-accounts']) ?>" class="btn btn-primary">Ver Detalhes</a>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="caixa reports">
                <i class="bi fs-1 bi-person-fill-gear"></i>
                <h3>Reports de Posts</h3>
                <p class="grafico"><?= $contentReportsCount ?></p>
                <a href="<?= Url::to(['/reports/reports-content']) ?>" class="btn btn-primary">Ver Detalhes</a>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="caixa reports">
                <i class="bi fs-1 bi-patch-check"></i>
                <h3>Badges</h3>
                <p class="grafico"><?= $badgesCount ?></p>
                <a href="<?= Url::to(['/badge']) ?>" class="btn btn-primary">Ver Detalhes</a>
            </div>
        </div>
    </div>
</div>