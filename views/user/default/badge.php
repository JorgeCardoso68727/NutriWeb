<?php

use app\assets\BadgeAsset;
use yii\helpers\Html;
use yii\helpers\Url;

BadgeAsset::register($this);
$this->title = 'NutriWeb - Sou Nutricionista';

$isAdmin = $isAdmin ?? false;
$pendingRequests = $pendingRequests ?? [];
$fullName = $fullName ?? '';
$hasLastPedido = $hasLastPedido ?? false;
$lastPedidoEstado = $lastPedidoEstado ?? '';
$lastPedidoPdf = $lastPedidoPdf ?? '';
?>

<div class="pedido-nutri-page">
    <div class="pedido-nutri-card<?= $isAdmin ? ' pedido-nutri-card-admin' : '' ?>">
        <div class="pedido-nutri-header">
            <h5 class="pedido-nutri-title"><?= $isAdmin ? 'Pedidos de Badge' : 'Pedido Badge nutricionista' ?></h5>
            <?php if ($isAdmin): ?>
                <div class="pedido-nutri-actions">
                    <i class="bi bi-x-lg fs-5 me-2" title="Rejeitar"></i>
                    <i class="bi bi-check-circle fs-5" title="Aprovar"></i>
                </div>
            <?php endif; ?>
        </div>

        <?php if (Yii::$app->session->hasFlash('Badge-success')): ?>
            <div class="alert alert-success mb-3"><?= Yii::$app->session->getFlash('Badge-success') ?></div>
        <?php endif; ?>
        <?php if (Yii::$app->session->hasFlash('Badge-error')): ?>
            <div class="alert alert-danger mb-3"><?= Yii::$app->session->getFlash('Badge-error') ?></div>
        <?php endif; ?>

        <?php if ($isAdmin): ?>
            <div class="pedido-nutri-body">
                <?php if (empty($pendingRequests)): ?>
                    <p class="mb-0 text-muted">Nao existem pedidos pendentes.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle pedido-nutri-table mb-0">
                            <thead>
                                <tr>
                                    <th>Utilizador</th>
                                    <th>PDF</th>
                                    <th>Data</th>
                                    <th>Acoes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingRequests as $request): ?>
                                    <tr>
                                        <td>@<?= Html::encode($request['username']) ?></td>
                                        <td>
                                            <a href="<?= Url::to('@web/' . ltrim($request['diploma_pdf'], '/')) ?>" target="_blank" rel="noopener">
                                                Ver PDF
                                            </a>
                                        </td>
                                        <td><?= Html::encode($request['created_at']) ?></td>
                                        <td class="pedido-nutri-actions-cell">
                                            <?= Html::beginForm(['/user/badge-review', 'id' => $request['id'], 'acao' => 'aprovar'], 'post', ['class' => 'd-inline']) ?>
                                            <button type="submit" class="btn btn-sm btn-success">Aceitar</button>
                                            <?= Html::endForm() ?>
                                            <?= Html::beginForm(['/user/badge-review', 'id' => $request['id'], 'acao' => 'rejeitar'], 'post', ['class' => 'd-inline']) ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Rejeitar</button>
                                            <?= Html::endForm() ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="pedido-nutri-body">

                <label class="form-label small fw-bold">Nome Completo:</label>
                <h5><b><?= Html::encode($fullName !== '' ? $fullName : 'Sem nome definido') ?></b></h5>

                <div class="text-center mt-5">
                    <p class="small text-muted mb-4">Baixar Certificado/diploma</p>

                    <?= Html::beginForm(['/user/badge'], 'post', ['enctype' => 'multipart/form-data', 'id' => 'badgeForm']) ?>
                    <input type="file" class="input_file" id="diplomaPdf" name="diplomaPdf" accept="application/pdf" required style="display: none;">
                    <label class="label_file" for="diplomaPdf">
                        <i class="bi bi-download display-1"></i>
                    </label>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-success">Enviar pedido</button>
                    </div>
                    <?= Html::endForm() ?>
                </div>

                <?php if ($hasLastPedido): ?>
                    <hr>
                    <h6 class="fw-bold">Ultimo pedido</h6>
                    <?php if ($lastPedidoEstado !== ''): ?>
                        <p class="mb-1">Estado: <span class="badge text-bg-secondary"><?= Html::encode($lastPedidoEstado) ?></span></p>
                    <?php endif; ?>
                    <?php if ($lastPedidoPdf !== ''): ?>
                        <p class="mb-0">
                            PDF enviado:
                            <a href="<?= Url::to('@web/' . ltrim($lastPedidoPdf, '/')) ?>" target="_blank" rel="noopener">
                                abrir diploma
                            </a>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <script>
            document.getElementById('diplomaPdf').addEventListener('change', function() {
                if (this.files.length > 0) {
                    document.getElementById('badgeForm').submit();
                }
            });
        </script>
    </div>
</div>