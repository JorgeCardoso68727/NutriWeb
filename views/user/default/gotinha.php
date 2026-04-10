<?php

use app\assets\GotinhaAsset;
use yii\bootstrap5\Html;
use yii\helpers\Url;

GotinhaAsset::register($this);
$this->title = 'Nutriweb - Lembrete de Agua';

$todayTotalMl = (int) ($todayTotalMl ?? 0);
$dailyGoalMl = (int) ($dailyGoalMl ?? 2000);
$progressPercent = (int) ($progressPercent ?? 0);
$recentEntries = is_array($recentEntries ?? null) ? $recentEntries : [];
?>

<div class="gotinha-page">
    <div class="gotinha-header">
        <h4 class="mb-1">Lembrete de tomar agua</h4>
        <p class="text-muted mb-0">Regista os teus copos ao longo do dia para manteres a hidratacao.</p>
    </div>

    <?php if (Yii::$app->session->hasFlash('Agua-success')): ?>
        <div class="alert alert-success py-2 px-3 mt-3 mb-0"><?= Html::encode(Yii::$app->session->getFlash('Agua-success')) ?></div>
    <?php endif; ?>

    <?php if (Yii::$app->session->hasFlash('Agua-error')): ?>
        <div class="alert alert-danger py-2 px-3 mt-3 mb-0"><?= Html::encode(Yii::$app->session->getFlash('Agua-error')) ?></div>
    <?php endif; ?>

    <div class="gotinha-grid">
        <section class="gotinha-card status-card">
            <div class="status-top">
                <img src="<?= Html::encode(Url::to('@web/Img/gotinha.png')) ?>" alt="Sr Gotinha" class="gotinha-illustration">
                <div>
                    <h5 class="mb-1">Hoje: <?= Html::encode((string) $todayTotalMl) ?> ml</h5>
                    <p class="text-muted mb-0">Meta diaria: <?= Html::encode((string) $dailyGoalMl) ?> ml</p>
                </div>
            </div>

            <div class="progress gotinha-progress" role="progressbar" aria-label="Progresso de agua" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= Html::encode((string) $progressPercent) ?>">
                <div class="progress-bar" style="width: <?= Html::encode((string) $progressPercent) ?>%;"><?= Html::encode((string) $progressPercent) ?>%</div>
            </div>

            <p class="progress-help mb-0">Quanta mais agua bebes, mais o Sr. Gotinha fica feliz.</p>
        </section>

        <section class="gotinha-card action-card">
            <h6 class="mb-2">Adicionar agua</h6>
            <p class="text-muted small mb-3">Escolhe uma quantidade rapida ou escreve um valor personalizado.</p>

            <div class="quick-actions">
                <?php foreach ([150, 200, 250, 500] as $amount): ?>
                    <?= Html::beginForm(['gotinha'], 'post', ['class' => 'quick-form']) ?>
                    <?= Html::hiddenInput('quantidade_ml', (string) $amount) ?>
                    <?= Html::submitButton('+' . $amount . ' ml', ['class' => 'btn btn-outline-success btn-sm w-100']) ?>
                    <?= Html::endForm() ?>
                <?php endforeach; ?>
            </div>

            <?= Html::beginForm(['gotinha'], 'post', ['class' => 'custom-form']) ?>
            <label for="quantidade-ml" class="form-label mb-1">Quantidade personalizada (ml)</label>
            <div class="input-group">
                <?= Html::input('number', 'quantidade_ml', '', [
                    'class' => 'form-control',
                    'id' => 'quantidade-ml',
                    'min' => '50',
                    'step' => '50',
                    'placeholder' => 'Ex: 300',
                    'required' => true,
                ]) ?>
                <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
            </div>
            <?= Html::endForm() ?>
        </section>

        <section class="gotinha-card history-card">
            <h6 class="mb-2">Ultimos registos</h6>
            <?php if (empty($recentEntries)): ?>
                <p class="text-muted mb-0">Ainda nao tens registos de agua.</p>
            <?php else: ?>
                <ul class="history-list">
                    <?php foreach ($recentEntries as $entry): ?>
                        <?php
                        $time = trim((string) ($entry['data_registo'] ?? ''));
                        $timeLabel = $time !== '' ? date('d/m H:i', strtotime($time)) : '-';
                        ?>
                        <li>
                            <span class="history-amount"><?= Html::encode((string) ((int) ($entry['quantidade_ml'] ?? 0))) ?> ml</span>
                            <span class="history-time"><?= Html::encode($timeLabel) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </div>
</div>
