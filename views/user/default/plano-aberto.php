<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\PlanoAsset;

/** @var \app\models\PlanoNutricional $plan */
/** @var string $tituloPlano */
/** @var array $dias */
/** @var string $imagemPlano */
/** @var string $autorUsername */

$this->title = 'NutriWeb - ' . $tituloPlano;
PlanoAsset::register($this);

$ordemDias = ['2ª', '3ª', '4ª', '5ª', '6ª', 'Sa', 'Do'];
// Garantir que o frontend mostra sempre os 7 dias, mesmo sem refeições.
foreach ($ordemDias as $diaPadrao) {
    if (!isset($dias[$diaPadrao]) || !is_array($dias[$diaPadrao])) {
        $dias[$diaPadrao] = [];
    }
}

$chavesOrdenadas = $ordemDias;



$perfilAutorUrl = Url::to('/' . $autorUsername);
$diaAtivo = !empty($chavesOrdenadas) ? (string) $chavesOrdenadas[0] : (string) $ordemDias[0];
$canDeletePlan = !Yii::$app->user->isGuest && (int) Yii::$app->user->id === (int) ($plan->user_id ?? 0);

$this->registerJs(<<<JS
(function () {
    const container = document.querySelector('[data-plano-open]');
    if (!container) {
        return;
    }

    const dayButtons = Array.from(container.querySelectorAll('[data-day-btn]'));
    const mealGroups = Array.from(container.querySelectorAll('[data-day-group]'));
    const activeLabel = container.querySelector('[data-active-day]');

    // Ativa o dia clicado e oculta os restantes grupos de refeições.
    function setActiveDay(day) {
        dayButtons.forEach(function (btn) {
            const isActive = btn.getAttribute('data-day-btn') === day;
            btn.classList.toggle('diaSele', isActive);
            btn.classList.toggle('dia', !isActive);
        });

        mealGroups.forEach(function (group) {
            const isVisible = group.getAttribute('data-day-group') === day;
            group.hidden = !isVisible;
        });

        if (activeLabel) {
            activeLabel.textContent = day;
        }
    }

    dayButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            setActiveDay(btn.getAttribute('data-day-btn'));
        });
    });

    setActiveDay('{$diaAtivo}');
})();
JS);
?>

<div class="plano-open-shell">
    <div class="container py-4 py-md-5">
        <div class="plano-open-page" data-plano-open>
            <div class="plano-open-hero">
                <h1 class="h3 plano-open-title"><?= Html::encode($tituloPlano) ?></h1>
                <div class="plano-open-actions">
                    <?php if ($canDeletePlan): ?>
                        <?= Html::beginForm(['/plan/delete', 'id' => (int) $plan->id], 'post', [
                            'class' => 'd-inline-block',
                            'onsubmit' => "return confirm('Tens a certeza que queres eliminar este plano?');",
                        ]) ?>
                        <button type="submit" class="plano-open-delete-btn" title="Eliminar plano" aria-label="Eliminar plano">
                            <i class="bi bi-trash"></i>
                        </button>
                        <?= Html::endForm() ?>
                    <?php endif; ?>

                    <?= Html::a('Voltar ao perfil', $perfilAutorUrl, ['class' => 'plano-open-back']) ?>
                </div>
            </div>

            <div class="DiasSemana plano-open-days">
                <?php foreach ($ordemDias as $diaPadrao): ?>
                    <button type="button" class="<?= $diaPadrao === $diaAtivo ? 'diaSele' : 'dia' ?>" data-day-btn="<?= Html::encode($diaPadrao) ?>"><?= Html::encode($diaPadrao) ?></button>
                <?php endforeach; ?>
            </div>

            <?php if (empty($chavesOrdenadas)): ?>
                <div class="alert alert-info mb-0">Este plano ainda nao tem refeicoes organizadas por dia.</div>
            <?php else: ?>
                <?php foreach ($chavesOrdenadas as $dia): ?>
                    <?php $refeicoes = is_array($dias[$dia] ?? null) ? $dias[$dia] : []; ?>
                    <div class="plano-open-day-group" data-day-group="<?= Html::encode((string) $dia) ?>" <?= $dia !== $diaAtivo ? ' hidden' : '' ?>>
                        <?php if (empty($refeicoes)): ?>
                            <p class="text-muted mb-0 plano-open-day-empty">Sem refeicoes registadas para este dia.</p>
                        <?php else: ?>
                            <div class="row g-4 align-items-stretch">
                                <?php foreach ($refeicoes as $refeicao): ?>
                                    <?php
                                    $label = trim((string) ($refeicao['label'] ?? 'Refeicao'));
                                    $descricao = trim((string) ($refeicao['description'] ?? ''));
                                    $imagem = trim((string) ($refeicao['image'] ?? ''));
                                    $imagemUrl = $imagem !== '' ? Url::to('@web/' . ltrim($imagem, '/')) : null;
                                    ?>
                                    <div class="col-12 col-md-6">
                                        <article class="post-card-preview plano-open-meal-card">
                                            <div class="post-header">
                                                <b><?= Html::encode($label !== '' ? $label : 'Refeicao') ?></b>
                                            </div>

                                            <?php if ($imagemUrl !== null): ?>
                                                <div class="plano-open-image-wrap">
                                                    <img src="<?= Html::encode($imagemUrl) ?>" alt="Imagem da refeicao" class="plano-open-meal-image">
                                                </div>
                                            <?php endif; ?>

                                            <p class="plano-open-description"><?= Html::encode($descricao !== '' ? $descricao : 'Sem descricao.') ?></p>
                                        </article>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>