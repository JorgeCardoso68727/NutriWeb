<?php

use app\assets\RecipeEventAsset;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Event $model */
/** @var int $participantCount */
/** @var bool $isRegistered */
/** @var bool $canEditEvent */
/** @var bool $canDeleteEvent */
/** @var bool $isAdminViewer */
/** @var bool $canReportEvent */

$this->title = Html::encode($model->title);
RecipeEventAsset::register($this);
$categoryName = $model->category->name ?? 'Sem categoria';
$creatorName = $model->creator->username ?? 'Utilizador';
?>

<div class="event-view container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="mb-3 d-flex gap-2 flex-wrap event-view-actions">
                <?= Html::a('Voltar aos eventos', ['/event/index'], ['class' => 'btn btn-outline-secondary event-back-btn']) ?>
                <?php if ($canEditEvent): ?>
                    <?= Html::a('Editar', ['/event/update', 'id' => $model->id], ['class' => 'btn btn-outline-success event-edit-btn']) ?>
                    <?php if ($model->status !== 'completed'): ?>
                        <?= Html::a('Marcar como concluído', ['/event/complete', 'id' => $model->id], ['class' => 'btn btn-outline-info event-complete-btn', 'onclick' => 'return confirm("Marcar este evento como concluído? Os participantes serão notificados.");']) ?>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if ($canDeleteEvent): ?>
                    <?= Html::beginForm(['/event/delete', 'id' => $model->id], 'post', ['class' => 'd-inline']) ?>
                    <button type="submit" class="btn btn-outline-danger event-delete-btn" onclick="return confirm('Eliminar este evento?')">
                        <?php if ($isAdminViewer): ?>
                            <i class="bi bi-hammer me-1"></i>Apagar
                        <?php else: ?>
                            Eliminar
                        <?php endif; ?>
                    </button>
                    <?= Html::endForm() ?>
                <?php endif; ?>
                <?php if (!empty($canReportEvent)): ?>
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#eventReportModal">
                        Reportar evento
                    </button>
                <?php endif; ?>
            </div>

            <div class="card border-0 shadow-sm event-detail-card">
                <div class="card-body p-4 p-md-5 event-detail-body">
                    <div class="mb-3 event-detail-heading">
                        <div>
                            <span class="badge event-badge mb-2"><?= Html::encode($categoryName) ?></span>
                            <h1 class="event-detail-title h3 mb-2"><?= Html::encode($model->title) ?></h1>
                            <p class="text-muted mb-0">Criado por <?= Html::encode($creatorName) ?></p>
                        </div>
                        <?php if ($model->status === 'completed'): ?>
                            <span class="badge event-status-badge">Concluído</span>
                        <?php endif; ?>
                    </div>

                    <div class="row g-3 mb-4 event-metrics">
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3 h-100 event-metric">
                                <small class="text-muted d-block">A partir de</small>
                                <strong><?= Html::encode((string) $model->start_date) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3 h-100 event-metric">
                                <small class="text-muted d-block">Até</small>
                                <strong><?= Html::encode((string) $model->end_date) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3 h-100 event-metric">
                                <small class="text-muted d-block">Local</small>
                                <strong><?= Html::encode((string) ($model->location ?: 'A definir')) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3 h-100 event-metric">
                                <small class="text-muted d-block">Participantes</small>
                                <strong><?= (int) $participantCount ?></strong>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h2 class="h5 mb-3 event-section-title">Descrição</h2>
                        <p class="mb-0"><?= nl2br(Html::encode($model->description)) ?></p>
                    </div>

                    <div class="d-flex flex-wrap gap-2 event-action-row">
                        <?php if ($model->status === 'completed'): ?>
                            <span class="badge text-bg-secondary align-self-center">Inscrições encerradas</span>
                        <?php elseif ($isRegistered): ?>
                            <?= Html::a('Cancelar inscrição', ['/event/unregister', 'id' => $model->id], ['class' => 'btn btn-outline-danger event-action-btn']) ?>
                        <?php else: ?>
                            <?= Html::a('Inscrever-me', ['/event/register', 'id' => $model->id], ['class' => 'btn btn-success event-action-btn']) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($canReportEvent)): ?>
    <div class="modal fade" id="eventReportModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="eventReportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="eventReportModalLabel">Reportar Evento</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?= Html::beginForm(['/reportar'], 'post', ['id' => 'event-report-form']) ?>
                    <?= Html::hiddenInput('target_type', 'event') ?>
                    <?= Html::hiddenInput('target_user_id', (int) $model->creator_id) ?>
                    <?= Html::hiddenInput('target_post_id', '') ?>
                    <?= Html::hiddenInput('target_event_id', (int) $model->id) ?>
                    <div class="mb-3">
                        <label for="event-report-motivo" class="form-label">Motivo do Reporte</label>
                        <select class="form-select" id="event-report-motivo" name="motivo" required>
                            <option value="">Seleciona um motivo...</option>
                            <option value="conteudo-inapropriado">Conteudo inapropriado</option>
                            <option value="informacao-falsa">Informacao falsa</option>
                            <option value="spam">Spam</option>
                            <option value="fraude">Fraude</option>
                            <option value="outro">Outro</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="event-report-descricao" class="form-label">Descricao</label>
                        <textarea class="form-control" id="event-report-descricao" name="descricao" rows="3" placeholder="Descreve o motivo do teu reporte..."></textarea>
                    </div>
                    <?= Html::endForm() ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success me-auto" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" form="event-report-form">Reportar</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>