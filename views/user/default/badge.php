<?php

use app\assets\BadgeAsset;
use yii\helpers\Html;
use yii\helpers\Url;

BadgeAsset::register($this);

$isAdmin = $isAdmin ?? false;
$pendingRequests = $pendingRequests ?? [];
$fullName = $fullName ?? '';
$hasLastPedido = $hasLastPedido ?? false;
$lastPedidoEstado = $lastPedidoEstado ?? '';
$lastPedidoPdf = $lastPedidoPdf ?? '';
$requestType = $requestType ?? 'nutricionista';
$isInstitutionType = $requestType === 'instituicao';
$this->title = 'NutriWeb - ' . ($isInstitutionType ? 'Sou Instituicao' : 'Sou Nutricionista');
?>

<div class="pedido-nutri-page">
    <div class="pedido-nutri-card<?= $isAdmin ? ' pedido-nutri-card-admin' : '' ?>">
        <div class="pedido-nutri-hero">
            <div>
                <h1 class="pedido-nutri-title"><?= $isAdmin ? ($isInstitutionType ? 'Pedidos de instituicao' : 'Pedidos de badge') : ($isInstitutionType ? 'Pedido de instituicao' : 'Pedido de badge nutricionista') ?></h1>
                <p class="pedido-nutri-subtitle"><?= $isAdmin ? 'Revê os pedidos enviados e aprova ou rejeita certificados.' : ($isInstitutionType ? 'Envia o documento da instituicao em PDF para validar o perfil institucional.' : 'Envia o teu diploma em PDF para validar o perfil de nutricionista.') ?></p>
            </div>

            <div class="pedido-nutri-actions">
                <a class="btn btn-outline-secondary btn-sm" href="<?= Url::to(['/badge/badge', 'tipo' => $requestType]) ?>" data-refresh-db="badge" data-refresh-url="<?= Url::to(['/badge/badge', 'refresh' => 1, 'tipo' => $requestType]) ?>" title="Atualizar">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </div>

        <?php if (Yii::$app->session->hasFlash('Badge-success')): ?>
            <div class="alert alert-success mb-3"><?= Yii::$app->session->getFlash('Badge-success') ?></div>
        <?php endif; ?>
        <?php if (Yii::$app->session->hasFlash('Badge-error')): ?>
            <div class="alert alert-danger mb-3"><?= Yii::$app->session->getFlash('Badge-error') ?></div>
        <?php endif; ?>

        <?php if ($isAdmin): ?>
            <div class="pedido-nutri-body">
                <div class="table-responsive">
                    <table class="table table-sm align-middle pedido-nutri-table mb-0">
                        <thead>
                            <tr>
                                <th><span id="badgePendingCount"><?= count($pendingRequests) ?></span> pedidos</th>
                                <th>Dados</th>
                                <th>PDF</th>
                                <th>Data</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody id="badgePendingTableBody">
                            <?php if (empty($pendingRequests)): ?>
                                <tr>
                                    <td colspan="5" class="text-muted">Nao existem pedidos pendentes.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pendingRequests as $request): ?>
                                    <tr>
                                        <td><?= Html::encode($request['username']) ?></td>
                                        <td>
                                            <?php if (!empty($request['observacao'])): ?>
                                                <?php
                                                $obsData = json_decode($request['observacao'], true);
                                                $tipo = $obsData['tipo'] ?? '';
                                                ?>
                                                <small>
                                                    <?php if ($tipo === 'instituicao'): ?>
                                                        <strong>Nome:</strong> <?= Html::encode($obsData['nomeCompleto'] ?? '') ?><br>
                                                        <strong>Instituição:</strong> <?= Html::encode($obsData['nomeInstituicao'] ?? '') ?><br>
                                                        <strong>NIF:</strong> <?= Html::encode($obsData['numeroFiscal'] ?? '') ?>
                                                    <?php else: ?>
                                                        <strong>Nome:</strong> <?= Html::encode($obsData['nomeCompleto'] ?? '') ?>
                                                    <?php endif; ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?= Url::to('@web/' . ltrim($request['diploma_pdf'], '/')) ?>" target="_blank" rel="noopener">
                                                Ver PDF
                                            </a>
                                        </td>
                                        <td><?= Html::encode($request['created_at']) ?></td>
                                        <td class="pedido-nutri-actions-cell">
                                            <?= Html::beginForm(['/badge/badge-review', 'id' => $request['id'], 'acao' => 'aprovar', 'tipo' => $requestType], 'post', ['class' => 'd-inline']) ?>
                                            <button type="submit" class="btn btn-sm btn-success">Aceitar</button>
                                            <?= Html::endForm() ?>
                                            <?= Html::beginForm(['/badge/badge-review', 'id' => $request['id'], 'acao' => 'rejeitar', 'tipo' => $requestType], 'post', ['class' => 'd-inline']) ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Rejeitar</button>
                                            <?= Html::endForm() ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="pedido-nutri-body">
                <?php if ($isInstitutionType): ?>
                    <!-- Formulário da Instituição -->
                    <div class="pedido-nutri-upload-block">
                        <p class="pedido-nutri-note mb-4">Preenche os dados da instituição e carrega um comprovativo em PDF para pedir validação.</p>

                        <?= Html::beginForm(['/badge', 'tipo' => $requestType], 'post', ['enctype' => 'multipart/form-data', 'id' => 'badgeForm', 'class' => 'needs-validation']) ?>

                        <div class="mb-3">
                            <label for="nomeCompleto" class="form-label fw-bold">Nome Completo</label>
                            <input type="text" class="form-control" id="nomeCompleto" name="nomeCompleto" placeholder="Introduz o teu nome completo" required>
                            <small class="form-text text-muted">O teu nome completo para fins de validação.</small>
                        </div>

                        <div class="mb-3">
                            <label for="nomeInstituicao" class="form-label fw-bold">Nome da Instituição</label>
                            <input type="text" class="form-control" id="nomeInstituicao" name="nomeInstituicao" placeholder="Introduz o nome da instituição" required>
                            <small class="form-text text-muted">Nome oficial da instituição.</small>
                        </div>

                        <div class="mb-3">
                            <label for="numeroFiscal" class="form-label fw-bold">Número Fiscal</label>
                            <input type="text" class="form-control" id="numeroFiscal" name="numeroFiscal" placeholder="Introduz o número fiscal" required inputmode="numeric" maxlength="9" pattern="[0-9]{9}" title="Introduz um NIF válido com 9 dígitos">
                            <small class="form-text text-muted">Número de identificação fiscal (NIF) da instituição.</small>
                        </div>

                        <div class="mb-3">
                            <label for="instituicaoPdf" class="form-label fw-bold">Comprovativo em PDF</label>
                            <input type="file" class="input_file" id="instituicaoPdf" name="instituicaoPdf" accept="application/pdf" required style="display: none;">
                            <label class="label_file" id="instituicaoPdfLabel" for="instituicaoPdf">
                                <i class="bi bi-file-earmark-arrow-up display-1"></i>
                                <span id="instituicaoPdfText">Escolher PDF</span>
                            </label>
                            <small id="instituicaoPdfName" class="form-text text-success d-block mt-2" style="display:none !important;"></small>
                            <small class="form-text text-muted d-block mt-2">Documento que comprova a situação regular da instituição.</small>
                        </div>

                        <div class="mt-4 text-center">
                            <button type="submit" class="btn btn-success btn-lg">Enviar pedido</button>
                        </div>
                        <?= Html::endForm() ?>
                    </div>
                <?php else: ?>
                    <!-- Formulário do Nutricionista -->
                    <div class="pedido-nutri-upload-block">
                        <p class="pedido-nutri-note mb-4">Carrega o teu diploma em PDF para pedir a validação como nutricionista.</p>

                        <?= Html::beginForm(['/badge', 'tipo' => $requestType], 'post', ['enctype' => 'multipart/form-data', 'id' => 'badgeForm', 'class' => 'needs-validation']) ?>

                        <div class="mb-3">
                            <label for="nomeCompletoNutricionista" class="form-label fw-bold">Nome Completo</label>
                            <input type="text" class="form-control" id="nomeCompletoNutricionista" name="nomeCompletoNutricionista" placeholder="Introduz o teu nome completo" required>
                            <small class="form-text text-muted">O teu nome completo para fins de validação.</small>
                        </div>

                        <div>
                            <label for="diplomaPdf" class="form-label fw-bold mb-3">Diploma em PDF</label>
                            <input type="file" class="input_file" id="diplomaPdf" name="diplomaPdf" accept="application/pdf" required style="display: none;">
                            <label class="label_file" id="diplomaPdfLabel" for="diplomaPdf">
                                <i class="bi bi-file-earmark-arrow-up display-1"></i>
                                <span id="diplomaPdfText">Escolher PDF</span>
                            </label>
                            <small id="diplomaPdfName" class="form-text text-success d-block mt-2" style="display:none !important;"></small>
                            <small class="form-text text-muted d-block mt-2">Documento que comprova a tua situação como nutricionista.</small>
                        </div>

                        <div class="mt-4 text-center">
                            <button type="submit" class="btn btn-success btn-lg">Enviar pedido</button>
                        </div>
                        <?= Html::endForm() ?>
                    </div>
                <?php endif; ?>

                <div id="badgeLastPedidoSection">
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
            </div>
        <?php endif; ?>

        <script>
            (function() {
                function escapeHtml(value) {
                    return String(value)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/\"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                }

                function bindPdfPickerFeedback(inputId, labelId, textId, nameId) {
                    var input = document.getElementById(inputId);
                    var label = document.getElementById(labelId);
                    var textNode = document.getElementById(textId);
                    var nameNode = document.getElementById(nameId);

                    if (!input || !label || !textNode || !nameNode) {
                        return;
                    }

                    input.addEventListener('change', function() {
                        if (!this.files || this.files.length === 0) {
                            textNode.textContent = 'Escolher PDF';
                            nameNode.textContent = '';
                            nameNode.style.display = 'none';
                            label.classList.remove('btn-success');
                            return;
                        }

                        var fileName = this.files[0].name || 'PDF selecionado';
                        textNode.textContent = 'PDF selecionado';
                        nameNode.textContent = fileName;
                        nameNode.style.display = 'block';
                        label.classList.add('btn-success');
                    });
                }

                bindPdfPickerFeedback('instituicaoPdf', 'instituicaoPdfLabel', 'instituicaoPdfText', 'instituicaoPdfName');
                bindPdfPickerFeedback('diplomaPdf', 'diplomaPdfLabel', 'diplomaPdfText', 'diplomaPdfName');

                // O envio deve ser manual via botão "Enviar pedido".

                var refreshButton = document.querySelector('[data-refresh-db="badge"]');
                if (!refreshButton) {
                    return;
                }

                var badgeForm = document.getElementById('badgeForm');
                var nifInput = document.getElementById('numeroFiscal');

                function isValidPortugueseNif(value) {
                    var digits = String(value || '').replace(/\D+/g, '');
                    if (digits.length !== 9) {
                        return false;
                    }

                    var sum = 0;
                    for (var i = 0; i < 8; i += 1) {
                        sum += parseInt(digits.charAt(i), 10) * (9 - i);
                    }

                    var remainder = sum % 11;
                    var checkDigit = remainder < 2 ? 0 : 11 - remainder;
                    return checkDigit === parseInt(digits.charAt(8), 10);
                }

                if (nifInput) {
                    nifInput.addEventListener('input', function() {
                        this.value = this.value.replace(/\D+/g, '').slice(0, 9);
                        this.setCustomValidity('');
                    });
                }

                if (badgeForm && nifInput) {
                    badgeForm.addEventListener('submit', function(event) {
                        var nif = nifInput.value || '';
                        if (!isValidPortugueseNif(nif)) {
                            event.preventDefault();
                            nifInput.setCustomValidity('O número fiscal (NIF) é inválido.');
                            nifInput.reportValidity();
                            return;
                        }

                        nifInput.value = nif.replace(/\D+/g, '');
                        nifInput.setCustomValidity('');
                    });
                }

                var isInstitutionType = <?= $isInstitutionType ? 'true' : 'false' ?>;
                var refreshResetTimer = null;

                function setRefreshButtonLoadingState() {
                    refreshButton.classList.add('disabled');
                    refreshButton.setAttribute('aria-busy', 'true');
                    refreshButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                }

                function setRefreshButtonSuccessState() {
                    refreshButton.classList.remove('disabled');
                    refreshButton.removeAttribute('aria-busy');
                    refreshButton.innerHTML = '<i class="bi bi-check-lg"></i>';

                    if (refreshResetTimer !== null) {
                        window.clearTimeout(refreshResetTimer);
                    }

                    refreshResetTimer = window.setTimeout(function() {
                        refreshButton.innerHTML = '<i class="bi bi-arrow-clockwise"></i>';
                    }, 1200);
                }

                function setRefreshButtonDefaultState() {
                    refreshButton.classList.remove('disabled');
                    refreshButton.removeAttribute('aria-busy');
                    refreshButton.innerHTML = '<i class="bi bi-arrow-clockwise"></i>';
                }

                refreshButton.addEventListener('click', async function(event) {
                    event.preventDefault();

                    var refreshUrl = refreshButton.getAttribute('data-refresh-url') || '';
                    if (!refreshUrl) {
                        return;
                    }

                    setRefreshButtonLoadingState();

                    try {
                        var response = await fetch(refreshUrl, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            credentials: 'same-origin'
                        });

                        if (!response.ok) {
                            setRefreshButtonDefaultState();
                            return;
                        }

                        var payload = await response.json();
                        if (!payload || !payload.success) {
                            setRefreshButtonDefaultState();
                            return;
                        }

                        if (payload.is_admin) {
                            var countNode = document.getElementById('badgePendingCount');
                            var tableBody = document.getElementById('badgePendingTableBody');
                            if (!countNode || !tableBody) {
                                setRefreshButtonDefaultState();
                                return;
                            }

                            var rows = Array.isArray(payload.rows) ? payload.rows : [];
                            countNode.textContent = String(payload.count || 0);

                            if (rows.length === 0) {
                                var colspan = isInstitutionType ? 5 : 4;
                                tableBody.innerHTML = '<tr><td colspan="' + colspan + '" class="text-muted">Nao existem pedidos pendentes.</td></tr>';
                                setRefreshButtonSuccessState();
                                return;
                            }

                            var html = '';
                            rows.forEach(function(row) {
                                html += '<tr>';
                                html += '<td>@' + escapeHtml(row.username || 'utilizador') + '</td>';
                                if (isInstitutionType) {
                                    html += '<td><small>';
                                    html += '<strong>Nome:</strong> ' + escapeHtml(row.nomeCompleto || '') + '<br>';
                                    html += '<strong>Instituição:</strong> ' + escapeHtml(row.nomeInstituicao || '') + '<br>';
                                    html += '<strong>NIF:</strong> ' + escapeHtml(row.numeroFiscal || '');
                                    html += '</small></td>';
                                }
                                html += '<td><a href="' + escapeHtml(row.pdf_url || '#') + '" target="_blank" rel="noopener">Ver PDF</a></td>';
                                html += '<td>' + escapeHtml(row.created_at || '') + '</td>';
                                html += '<td class="pedido-nutri-actions-cell">';
                                html += '<form method="post" action="' + escapeHtml(row.approve_url || '#') + '" class="d-inline">';
                                html += '<input type="hidden" name="_csrf" value="' + escapeHtml(yii.getCsrfToken()) + '">';
                                html += '<button type="submit" class="btn btn-sm btn-success">Aceitar</button>';
                                html += '</form>';
                                html += '<form method="post" action="' + escapeHtml(row.reject_url || '#') + '" class="d-inline">';
                                html += '<input type="hidden" name="_csrf" value="' + escapeHtml(yii.getCsrfToken()) + '">';
                                html += '<button type="submit" class="btn btn-sm btn-outline-danger">Rejeitar</button>';
                                html += '</form>';
                                html += '</td>';
                                html += '</tr>';
                            });

                            tableBody.innerHTML = html;
                            setRefreshButtonSuccessState();
                            return;
                        }

                        var lastPedidoSection = document.getElementById('badgeLastPedidoSection');
                        if (!lastPedidoSection) {
                            setRefreshButtonDefaultState();
                            return;
                        }

                        if (!payload.has_last_pedido) {
                            lastPedidoSection.innerHTML = '';
                            setRefreshButtonSuccessState();
                            return;
                        }

                        var html = '<hr><h6 class="fw-bold">Ultimo pedido</h6>';
                        if (payload.last_pedido_estado) {
                            html += '<p class="mb-1">Estado: <span class="badge text-bg-secondary">' + escapeHtml(payload.last_pedido_estado) + '</span></p>';
                        }
                        if (payload.last_pedido_pdf_url) {
                            html += '<p class="mb-0">PDF enviado: <a href="' + escapeHtml(payload.last_pedido_pdf_url) + '" target="_blank" rel="noopener">abrir diploma</a></p>';
                        }

                        lastPedidoSection.innerHTML = html;
                        setRefreshButtonSuccessState();
                    } catch (error) {
                        // Keep current data on refresh failures.
                        setRefreshButtonDefaultState();
                    }
                });
            })();
        </script>
    </div>
</div>