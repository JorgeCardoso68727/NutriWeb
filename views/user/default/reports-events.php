<?php

/** @var yii\web\View $this */
/** @var array $eventReports */
/** @var int $eventReportsCount */
/** @var int $eventReportsReviewedCount */
/** @var array $eventReportsDailyLabels */
/** @var array $eventReportsDailyCounts */

use app\assets\DashboardAsset;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

DashboardAsset::register($this);
$this->title = 'Reports de eventos';

$eventReportsReviewedCount = (int) ($eventReportsReviewedCount ?? 0);
$eventReportsTotal = max(1, (int) $eventReportsCount + $eventReportsReviewedCount);
$eventReportsPendingPercent = (int) round(((int) $eventReportsCount / $eventReportsTotal) * 100);
$eventReportsReviewedPercent = (int) round(($eventReportsReviewedCount / $eventReportsTotal) * 100);
$eventReportsDailyLabels = is_array($eventReportsDailyLabels ?? null) ? $eventReportsDailyLabels : [];
$eventReportsDailyCounts = is_array($eventReportsDailyCounts ?? null) ? $eventReportsDailyCounts : [];
$eventReportsPeriodTotal = array_sum($eventReportsDailyCounts);
?>

<div class="dashboard-container reports-page">
    <div class="Dasheboard d-flex align-items-center justify-content-between">
        <h1><b>Reports de eventos</b></h1>
        <a class="btn btn-outline-secondary btn-sm" href="<?= Url::to(['/reports/reports-events']) ?>" data-refresh-db="reports-events" data-refresh-url="<?= Url::to(['/reports/reports-events', 'refresh' => 1]) ?>" title="Atualizar">
            <i class="bi bi-arrow-clockwise"></i>
        </a>
    </div>

    <div class="grid">
        <div class="caixa">
            <div class="reports">
                <p><b id="eventReportsCount"><?= (int) $eventReportsCount ?></b><br> Event Reports</p>
                <i class="bi fs-1 bi-calendar-x"></i>
            </div>
        </div>
        <div class="grafico-image">
            <div class="card border-0 shadow-sm p-3">
                <h6 class="mb-3">Reports por dia</h6>
                <div class="small text-muted mb-2 d-flex justify-content-between">
                    <span>Ultimos 7 dias</span>
                    <span>Total reports no periodo: <b id="eventReportsPeriodTotalLabel"><?= (int) $eventReportsPeriodTotal ?></b></span>
                </div>
                <div class="bg-light rounded-3 p-2">
                    <svg
                        id="eventReportsLineChart"
                        width="100%"
                        height="220"
                        viewBox="0 0 800 220"
                        class="d-block w-100"
                        preserveAspectRatio="none"
                        data-labels='<?= Html::encode(Json::encode($eventReportsDailyLabels)) ?>'
                        data-counts='<?= Html::encode(Json::encode($eventReportsDailyCounts)) ?>'>
                    </svg>
                    <div class="small text-muted d-flex justify-content-between px-1" id="eventReportsLineChartAxis"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Evento reportado</th>
                        <th scope="col">Reportou</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Acao</th>
                    </tr>
                </thead>
                <tbody id="eventReportsTableBody">
                    <?php if (empty($eventReports)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">Nao existem reports de eventos.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($eventReports as $index => $report): ?>
                            <?php
                            $estadoRevisao = trim((string) ($report['estado_revisao'] ?? 'pendente'));
                            $isRevisto = $estadoRevisao === 'revisto';
                            $reporterUserName = trim((string) ($report['reporter_username'] ?? ''));
                            $eventId = (int) ($report['event_id'] ?? $report['target_event_id'] ?? 0);
                            $eventLabel = trim((string) ($report['title'] ?? ''));
                            if ($eventLabel === '') {
                                $eventLabel = $eventId > 0 ? ('Evento #' . $eventId) : 'Evento removido';
                            }
                            ?>
                            <tr>
                                <th scope="row"><?= $index + 1 ?></th>
                                <td>
                                    <div><?= Html::encode($eventLabel) ?></div>
                                    <small class="text-muted"><?= Html::encode(($report['reportado_username'] ?? '') !== '' ? $report['reportado_username'] : 'Criador removido') ?></small>
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
                                        <a class="btn btn-primary btn-sm" href="<?= Url::to(['/event/view', 'id' => $eventId]) ?>">Abrir evento</a>
                                        <?php if (!$isRevisto): ?>
                                            <?= Html::beginForm(['/reports/mark-event-report-reviewed', 'id' => (int) $report['report_id']], 'post', ['class' => 'd-inline']) ?>
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

<?php
$this->registerJs(<<<'JS'
(function () {
    var refreshButton = document.querySelector('[data-refresh-db="reports-events"]');
    var countNode = document.getElementById('eventReportsCount');
    var tableBody = document.getElementById('eventReportsTableBody');
    var periodTotalLabelNode = document.getElementById('eventReportsPeriodTotalLabel');
    var lineChartNode = document.getElementById('eventReportsLineChart');
    var lineChartAxisNode = document.getElementById('eventReportsLineChartAxis');
    if (!refreshButton || !countNode || !tableBody || !periodTotalLabelNode || !lineChartNode || !lineChartAxisNode) {
        return;
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderLineChart(labels, counts) {
        var safeLabels = Array.isArray(labels) ? labels : [];
        var safeCounts = Array.isArray(counts) ? counts.map(function (v) { return Number(v) || 0; }) : [];

        if (safeLabels.length === 0 || safeCounts.length === 0) {
            lineChartNode.innerHTML = '';
            lineChartAxisNode.innerHTML = '';
            return;
        }

        var width = Math.max(360, lineChartNode.clientWidth || 0);
        var height = 220;
        var left = 26;
        var right = 10;
        var top = 14;
        var bottom = 28;
        var chartWidth = width - left - right;
        var chartHeight = height - top - bottom;
        var maxCount = Math.max.apply(null, safeCounts.concat([1]));

        lineChartNode.setAttribute('viewBox', '0 0 ' + width + ' ' + height);

        var points = safeCounts.map(function (count, index) {
            var x = left + ((safeCounts.length === 1 ? 0 : index / (safeCounts.length - 1)) * chartWidth);
            var y = top + (chartHeight - ((count / maxCount) * chartHeight));
            return { x: x, y: y, count: count };
        });

        var path = points.map(function (point, index) {
            return (index === 0 ? 'M' : 'L') + point.x.toFixed(2) + ' ' + point.y.toFixed(2);
        }).join(' ');

        var circles = points.map(function (point) {
            return '<circle cx="' + point.x.toFixed(2) + '" cy="' + point.y.toFixed(2) + '" r="3" fill="#0d6efd"></circle>';
        }).join('');

        var guides = [0.25, 0.5, 0.75, 1].map(function (ratio) {
            var y = top + (chartHeight - (ratio * chartHeight));
            return '<line x1="' + left + '" y1="' + y.toFixed(2) + '" x2="' + (width - right) + '" y2="' + y.toFixed(2) + '" stroke="#e9ecef" stroke-width="1"></line>';
        }).join('');

        lineChartNode.innerHTML =
            '<rect x="0" y="0" width="' + width + '" height="' + height + '" fill="transparent"></rect>' +
            guides +
            '<path d="' + path + '" fill="none" stroke="#0d6efd" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path>' +
            circles;

        var firstLabel = safeLabels[0] || '';
        var midLabel = safeLabels[Math.floor((safeLabels.length - 1) / 2)] || '';
        var lastLabel = safeLabels[safeLabels.length - 1] || '';
        lineChartAxisNode.innerHTML = '<span>' + escapeHtml(firstLabel) + '</span><span>' + escapeHtml(midLabel) + '</span><span>' + escapeHtml(lastLabel) + '</span>';
    }

    try {
        var initialLabels = JSON.parse(lineChartNode.dataset.labels || '[]');
        var initialCounts = JSON.parse(lineChartNode.dataset.counts || '[]');
        renderLineChart(initialLabels, initialCounts);
    } catch (error) {
        renderLineChart([], []);
    }

    window.addEventListener('resize', function () {
        try {
            var labels = JSON.parse(lineChartNode.dataset.labels || '[]');
            var counts = JSON.parse(lineChartNode.dataset.counts || '[]');
            renderLineChart(labels, counts);
        } catch (error) {
            renderLineChart([], []);
        }
    });

    refreshButton.addEventListener('click', async function (event) {
        event.preventDefault();

        var refreshUrl = refreshButton.getAttribute('data-refresh-url') || '';
        if (!refreshUrl) {
            return;
        }

        refreshButton.classList.add('disabled');

        try {
            var response = await fetch(refreshUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                return;
            }

            var payload = await response.json();
            if (!payload || !payload.success) {
                return;
            }

            var pending = Number(payload.count || 0);
            var dailyCounts = Array.isArray(payload.daily_counts) ? payload.daily_counts : [];
            var periodTotal = dailyCounts.reduce(function (sum, value) {
                return sum + (Number(value) || 0);
            }, 0);

            countNode.textContent = String(pending);
            periodTotalLabelNode.textContent = String(periodTotal);

            lineChartNode.dataset.labels = JSON.stringify(payload.daily_labels || []);
            lineChartNode.dataset.counts = JSON.stringify(dailyCounts);

            renderLineChart(payload.daily_labels || [], dailyCounts);

            var rows = Array.isArray(payload.rows) ? payload.rows : [];
            if (rows.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Nao existem reports de eventos.</td></tr>';
                return;
            }

            var html = '';
            rows.forEach(function (row, index) {
                html += '<tr>';
                html += '<th scope="row">' + String(index + 1) + '</th>';
                html += '<td><div>' + escapeHtml(row.event_label || 'Evento removido') + '</div><small class="text-muted">' + escapeHtml(row.reportado || 'Criador removido') + '</small></td>';
                html += '<td>' + escapeHtml(row.reportou || 'Utilizador removido') + '</td>';
                html += '<td><span class="badge text-bg-warning">' + escapeHtml(row.estado || 'Pendente') + '</span></td>';
                html += '<td><div class="d-flex flex-wrap gap-2">';
                html += '<a class="btn btn-primary btn-sm" href="' + escapeHtml(row.event_url || '#') + '">Abrir evento</a>';
                html += '<form method="post" action="' + escapeHtml(row.mark_reviewed_url || '#') + '" class="d-inline">';
                html += '<input type="hidden" name="_csrf" value="' + escapeHtml(yii.getCsrfToken()) + '">';
                html += '<button type="submit" class="btn btn-outline-success btn-sm">Marcar revisto</button>';
                html += '</form>';
                html += '</div></td>';
                html += '</tr>';
            });

            tableBody.innerHTML = html;
        } catch (error) {
            // Keep current data on refresh failures.
        } finally {
            refreshButton.classList.remove('disabled');
        }
    });
})();
JS);
?>