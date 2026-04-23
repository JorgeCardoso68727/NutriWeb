<?php

/** @var yii\web\View $this */
/** @var array $contentReports */
/** @var int $contentReportsCount */

use app\assets\DashboardAsset;
use yii\helpers\Html;
use yii\helpers\Url;

DashboardAsset::register($this);
$this->title = 'Reports de conteudo';
?>

<div class="dashboard-container reports-page">
    <div class="Dasheboard">
        <h1><b>Reports de posts</b></h1>
    </div>

    <div class="grid">
        <div class="caixa">
            <div class="reports">
                <p><b><?= (int) $contentReportsCount ?></b><br> Content Reports</p>
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
                        <th scope="col">Estado</th>
                        <th scope="col">Banir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($contentReports)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">Nao existem reports de conteudo.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($contentReports as $index => $report): ?>
                            <?php
                            $estadoRevisao = trim((string) ($report['estado_revisao'] ?? 'pendente'));
                            $isRevisto = $estadoRevisao === 'revisto';
                            $reportedUserName = trim((string) ($report['reportado_username'] ?? ''));
                            $reporterUserName = trim((string) ($report['reporter_username'] ?? ''));
                            $postId = (int) ($report['post_id'] ?? $report['target_post_id'] ?? 0);
                            $postLabel = trim((string) ($report['titulo'] ?? ''));
                            if ($postLabel === '') {
                                $postLabel = $postId > 0 ? ('Post #' . $postId) : 'Post removido';
                            }
                            ?>
                            <tr>
                                <th scope="row"><?= $index + 1 ?></th>
                                <td>
                                    <div><?= Html::encode($reportedUserName !== '' ? $reportedUserName : 'Utilizador removido') ?></div>
                                    <small class="text-muted"><?= Html::encode($postLabel) ?></small>
                                </td>
                                <td><?= Html::encode($reporterUserName !== '' ? $reporterUserName : 'Utilizador removido') ?></td>
                                <td>
                                    <?php if ($isRevisto): ?>
                                        <span class="badge text-bg-success">Revisto</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-warning">Pendente</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a class="btn btn-primary btn-sm" href="<?= Url::to(['/homepage/post-aberto', 'id' => (int) $report['post_id'], 'rever' => 1]) ?>">Rever Pedido...</a>
                                        <?php if (!$isRevisto): ?>
                                            <?= Html::beginForm(['/reports/mark-post-report-reviewed', 'id' => (int) $report['report_id']], 'post', ['class' => 'd-inline']) ?>
                                            <button type="submit" class="btn btn-outline-success btn-sm">Marcar revisto</button>
                                            <?= Html::endForm() ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>