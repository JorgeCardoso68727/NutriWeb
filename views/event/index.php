<?php

use app\assets\RecipeEventAsset;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var ActiveDataProvider $dataProvider */
/** @var array $categories */
/** @var array $filterModel */
/** @var bool $canCreateEvent */

$this->title = 'Eventos';
RecipeEventAsset::register($this);
$models = $dataProvider->getModels();
$pagination = $dataProvider->pagination;
$registeredEvents = $registeredEvents ?? [];
?>

<div class="event-index py-5">
    <div class="container">
        <div class="event-hero d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="event-title mb-1"><?= Html::encode($this->title) ?></h1>
                <p class="event-subtitle mb-0">Workshops, palestras, sessões de sensibilização e feiras criadas pela comunidade.</p>
            </div>
            <?php if ($canCreateEvent): ?>
                <div>
                    <?= Html::a('Criar evento', ['/event/create'], ['class' => 'btn btn-success btn-lg event-create-btn']) ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($registeredEvents)): ?>
            <div class="mb-5">
                <h2 class="event-title mb-3" style="font-size: 1.35rem;">Os Meus Eventos</h2>
                <div class="row g-4">
                    <?php foreach ($registeredEvents as $event): ?>
                        <?php
                        $eventId = (int) $event->id;
                        $eventUrl = Url::to(['/event/view', 'id' => $eventId]);
                        $title = trim((string) $event->title) !== '' ? trim((string) $event->title) : 'Evento';
                        $categoryName = $event->category->name ?? 'Sem categoria';
                        $creatorName = $event->creator->username ?? 'Utilizador';
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 shadow-sm border-0 event-card" style="background-color: #f0f8f0;">
                                <div class="card-body d-flex flex-column event-card-body">
                                    <div class="mb-2">
                                        <span class="badge event-badge mb-2"><?= Html::encode($categoryName) ?></span>
                                        <h5 class="card-title mb-1 event-card-title"><?= Html::a(Html::encode($title), $eventUrl, ['class' => 'text-decoration-none stretched-link event-card-link']) ?></h5>
                                        <p class="text-muted small mb-0">Organizado por <?= Html::encode($creatorName) ?></p>
                                    </div>
                                    <p class="card-text flex-grow-1 event-card-text"><?= Html::encode(mb_strimwidth((string) $event->description, 0, 140, '...')) ?></p>
                                    <div class="small text-muted mb-3 event-card-meta">
                                        <div><i class="bi bi-geo-alt me-1"></i><?= Html::encode((string) ($event->location ?: 'Local a definir')) ?></div>
                                        <div><i class="bi bi-calendar-event me-1"></i><?= Html::encode((string) $event->start_date) ?></div>
                                    </div>
                                    <div class="mt-auto d-flex gap-2">
                                        <?= Html::a('Ver evento', $eventUrl, ['class' => 'btn btn-success btn-sm']) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="event-filter card border-0 shadow-sm mb-4">
            <div class="card-body p-4 p-lg-5">
                <h3 class="mb-4">Explorar Eventos</h3>
                <?= Html::beginForm(['/event/index'], 'get', ['class' => 'row g-3 align-items-end event-filter-form']) ?>
                <div class="col-md-4">
                    <label class="form-label">Categoria</label>
                    <select name="category_id" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int) $category->id ?>" <?= (string) ($filterModel['category_id'] ?? '') === (string) $category->id ? 'selected' : '' ?>>
                                <?= Html::encode($category->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Localização</label>
                    <input type="text" name="location" class="form-control location-autocomplete" value="<?= Html::encode((string) ($filterModel['location'] ?? '')) ?>" placeholder="Ex: Lisboa">
                </div>
                <div class="col-md-2">
                    <label class="form-label">A partir de</label>
                    <input type="text" name="start_date" class="form-control filter-datetime" value="<?= Html::encode((string) ($filterModel['start_date'] ?? '')) ?>" placeholder="YYYY-MM-DD">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Até</label>
                    <input type="text" name="end_date" class="form-control filter-datetime" value="<?= Html::encode((string) ($filterModel['end_date'] ?? '')) ?>" placeholder="YYYY-MM-DD">
                </div>
                <div class="col-md-3 form-check ms-2">
                    <input type="checkbox" name="has_space" value="1" class="form-check-input" id="has_space" <?= !empty($filterModel['has_space']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="has_space">Apenas com vagas</label>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-dark">Filtrar</button>
                    <a href="<?= Url::to(['/event/index']) ?>" class="btn btn-outline-secondary">Limpar filtros</a>
                </div>
                <?= Html::endForm() ?>
            </div>
        </div>

        <div class="row g-4 event-grid">
            <?php if (empty($models)): ?>
                <div class="col-12">
                    <div class="alert alert-light border event-empty-state">Ainda não existem eventos para mostrar.</div>
                </div>
            <?php else: ?>
                <?php foreach ($models as $event): ?>
                    <?php
                    $eventId = (int) $event->id;
                    $eventUrl = Url::to(['/event/view', 'id' => $eventId]);
                    $title = trim((string) $event->title) !== '' ? trim((string) $event->title) : 'Evento';
                    $categoryName = $event->category->name ?? 'Sem categoria';
                    $creatorName = $event->creator->username ?? 'Utilizador';
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm border-0 event-card">
                            <div class="card-body d-flex flex-column event-card-body">
                                <div class="mb-2">
                                    <span class="badge event-badge mb-2"><?= Html::encode($categoryName) ?></span>
                                    <h5 class="card-title mb-1 event-card-title"><?= Html::a(Html::encode($title), $eventUrl, ['class' => 'text-decoration-none stretched-link event-card-link']) ?></h5>
                                    <p class="text-muted small mb-0">Organizado por <?= Html::encode($creatorName) ?></p>
                                </div>
                                <p class="card-text flex-grow-1 event-card-text"><?= Html::encode(mb_strimwidth((string) $event->description, 0, 140, '...')) ?></p>
                                <div class="small text-muted mb-3 event-card-meta">
                                    <div><i class="bi bi-geo-alt me-1"></i><?= Html::encode((string) ($event->location ?: 'Local a definir')) ?></div>
                                    <div><i class="bi bi-calendar-event me-1"></i><?= Html::encode((string) $event->start_date) ?></div>
                                </div>
                                <div class="mt-auto d-flex gap-2">
                                    <?= Html::a('Ver evento', $eventUrl, ['class' => 'btn btn-outline-success btn-sm event-card-btn']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($pagination !== false): ?>
            <div class="container mt-4 d-flex justify-content-center">
                <?= LinkPager::widget(['pagination' => $pagination]) ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$this->registerJs(
    <<<'JS'
if (typeof flatpickr !== 'undefined') {
    flatpickr('.filter-datetime', {
        enableTime: false,
        dateFormat: 'Y-m-d',
        allowInput: true,
    });
}
JS
);
?>