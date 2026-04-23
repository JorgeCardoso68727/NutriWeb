<?php

/** @var yii\web\View $this */
/** @var array $accountReports */
/** @var int $accountReportsCount */

use app\assets\DashboardAsset;
use yii\helpers\Html;
use yii\helpers\Url;

DashboardAsset::register($this);
$this->title = 'Reports de contas';
?>

<div class="dashboard-container reports-page">
    <div class="Dasheboard">
        <h1><b>Reports de contas</b></h1>
    </div>

    <div class="grid">
        <div class="caixa">
            <div class="reports">
                <p><b><?= (int) $accountReportsCount ?></b><br> Account Reports</p>
                <i class="bi fs-1 bi-person-fill-gear"></i>
            </div>
        </div>
        <div class="grafico-image">
            <img src="<?= Url::to('@web/Img/grafio.jpg') ?>" alt="Grafico de reports" class="usser-report" onerror="this.style.display='none'">
        </div>
    </div>

    <div class="main-content">
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Reportado</th>
                        <th scope="col">Reportou</th>
                        <th scope="col">Banir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($accountReports)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">Nao existem reports de contas.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($accountReports as $index => $report): ?>
                            <tr>
                                <th scope="row"><?= $index + 1 ?></th>
                                <td><?= Html::encode($report['reportado_username']) ?></td>
                                <td><?= Html::encode($report['reporter_username'] ?: 'Sistema') ?></td>
                                <td>
                                    <a class="btn btn-primary btn-sm" href="<?= Url::to('/' . $report['reportado_username'] . '?rever=1') ?>">Rever Pedido...</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>