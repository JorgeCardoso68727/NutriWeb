<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\db\Query;
use app\assets\PlanoAsset;

PlanoAsset::register($this);
$this->title = 'NutriWeb - Criar Plano Semanal';
$selectedDay = (string) ($selectedDay ?? '2ª');
$nomePlano = trim((string) ($nomePlano ?? ''));
$imagemPlano = trim((string) ($imagemPlano ?? ''));
$days = ['2ª', '3ª', '4ª', '5ª', '6ª', 'Sa', 'Do'];
$selectedDayLabel = $selectedDay;
$profileHref = !Yii::$app->user->isGuest ? Url::to('/' . Yii::$app->user->identity->username) : Url::to(['/perfil']);

$showCreatePlanLink = false;
if (!Yii::$app->user->isGuest) {
    $currentUserId = (int) Yii::$app->user->id;

    $roleSchema = Yii::$app->db->schema->getTableSchema('role', true);
    if ($roleSchema !== null && isset($roleSchema->columns['can_nutricionista'])) {
        $permissionValue = (new Query())
            ->select(['r.can_nutricionista'])
            ->from(['u' => 'user'])
            ->innerJoin(['r' => 'role'], 'r.id = u.role_id')
            ->where(['u.id' => $currentUserId])
            ->scalar();

        $showCreatePlanLink = (int) $permissionValue === 1;
    }
}

$this->beginPage();
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NutriWeb - Criar Plano Semanal</title>
    <?= Html::csrfMetaTags() ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= Html::encode(Url::to('@web/css/planosemanal.css')) ?>">
    <?php $this->head() ?>
</head>

<body class="body-Principal">
    <?php $this->beginBody() ?>

    <?= Html::beginForm(['/plan/criar-plano-semanal'], 'post', ['enctype' => 'multipart/form-data', 'id' => 'plano-form']) ?>


    <div class="main-layout">
        <div class="container py-4">
            <?php if (Yii::$app->session->hasFlash('Plan-success')): ?>
                <div class="alert alert-success mb-3"><?= Html::encode(Yii::$app->session->getFlash('Plan-success')) ?></div>
            <?php endif; ?>
            <?php if (Yii::$app->session->hasFlash('Plan-error')): ?>
                <div class="alert alert-danger mb-3"><?= Html::encode(Yii::$app->session->getFlash('Plan-error')) ?></div>
            <?php endif; ?>

            <div class="plano mb-4">
                <h2 class="text-center mb-0">Criar plano Alimentar</h2>
                <button type="submit" class="btn ms-5 criarPlano" aria-label="Guardar plano">
                    <i class="bi bi-check-circle fs-3"></i>
                </button>
            </div>

            <div class="text-center mb-3 selected-day-display">
                <span class="badge text-bg-success px-3 py-2">Dia selecionado: <span id="selected-day-label"><?= Html::encode($selectedDayLabel) ?></span></span>
            </div>

            <input type="hidden" name="diaSelecionado" id="diaSelecionado" value="<?= Html::encode($selectedDay) ?>">
            <input type="hidden" name="nomePlano" value="<?= Html::encode($nomePlano) ?>">

            <div class="DiasSemana mb-5" id="days-selector">
                <?php foreach ($days as $day): ?>
                    <button type="button" class="dia<?= $day === $selectedDay ? ' diaSele' : '' ?>" data-day="<?= Html::encode($day) ?>"><?= Html::encode($day) ?></button>
                <?php endforeach; ?>
            </div>

            <div class="row g-4 align-items-stretch" id="meal-list">
                <div class="col-md-5 d-flex align-items-center justify-content-center meal-add-wrapper" id="add-meal-wrapper">
                    <button type="button" class="btn adicionarRefeicao" id="add-meal" aria-label="Adicionar refeição">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
            </div>

            <template id="meal-template">
                <div class="col-md-5 meal-wrapper" data-meal-item>
                    <div class="post-card-preview meal-card">
                        <div class="post-header">
                            <b data-meal-label>3º Refeição</b>
                            <button type="button" class="btn p-0 ms-auto fs-4 remove-meal" aria-label="Remover refeição">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>

                        <div class="image-upload-area" data-upload-area>
                            <i class="bi bi-plus-lg"></i>
                            <img src="" alt="" style="display: none;">
                            <input type="file" name="mealImages[]" hidden accept="image/*">
                        </div>

                        <textarea class="post-textarea" name="mealDescriptions[]" placeholder="Insira a descrição..."></textarea>
                        <input type="hidden" name="mealLabels[]" value="3º Refeição">
                        <input type="hidden" name="mealDays[]" value="">
                    </div>
                </div>
            </template>
        </div>
    </div>
    <?= Html::endForm() ?>

    <?php
    $js = <<<'JS'
(function () {
    const daysSelector = document.getElementById('days-selector');
    const selectedDayInput = document.getElementById('diaSelecionado');
    const selectedDayLabel = document.getElementById('selected-day-label');
    const mealList = document.getElementById('meal-list');
    const addButton = document.getElementById('add-meal');
    const template = document.getElementById('meal-template');

    // Estado para guardar refeições por dia
    const mealsByDay = {};
    const days = ['2ª', '3ª', '4ª', '5ª', '6ª', 'Sa', 'Do'];

    function normalizeDay(day) {
        const value = String(day || '').trim();
        return days.includes(value) ? value : '2ª';
    }

    function ordinalLabel(index) {
        return index + 'º Refeição';
    }

    function updateLabels() {
        const mealWrappers = mealList.querySelectorAll('[data-meal-item]');
        let mealIndex = 1;

        mealWrappers.forEach(function (wrapper) {
            if (wrapper.id === 'add-meal-wrapper') {
                return;
            }

            const label = wrapper.querySelector('[data-meal-label]');
            const hiddenLabel = wrapper.querySelector('input[name="mealLabels[]"]');
            if (label) {
                label.textContent = ordinalLabel(mealIndex);
            }
            if (hiddenLabel) {
                hiddenLabel.value = ordinalLabel(mealIndex);
            }

            mealIndex += 1;
        });
    }

    function wireUploadArea(wrapper) {
        const uploadArea = wrapper.querySelector('[data-upload-area]');
        const fileInput = wrapper.querySelector('input[type="file"]');
        const previewImage = wrapper.querySelector('img');
        const icon = uploadArea.querySelector('i');

        if (!uploadArea || !fileInput || !previewImage) {
            return;
        }

        uploadArea.addEventListener('click', function () {
            fileInput.click();
        });

        fileInput.addEventListener('change', function (event) {
            const file = event.target.files && event.target.files[0];
            if (!file) {
                previewImage.style.display = 'none';
                previewImage.src = '';
                if (icon) icon.style.display = 'block';
                return;
            }

            const reader = new FileReader();
            reader.onload = function (readerEvent) {
                previewImage.src = String(readerEvent.target.result || '');
                previewImage.style.display = 'block';
                if (icon) {
                    icon.style.display = 'none';
                }
            };
            reader.readAsDataURL(file);
        });
    }

    function wireRemoveButton(wrapper) {
        const removeButton = wrapper.querySelector('.remove-meal');
        if (!removeButton) {
            return;
        }

        removeButton.addEventListener('click', function () {
            wrapper.remove();
            updateLabels();
        });
    }

    function saveMealsForDay(day) {
        const canonicalDay = normalizeDay(day);
        const mealWrappers = mealList.querySelectorAll('[data-meal-item]');
        const meals = [];

        mealWrappers.forEach(function (wrapper) {
            if (wrapper.id === 'add-meal-wrapper') {
                return;
            }

            const description = wrapper.querySelector('textarea[name="mealDescriptions[]"]');
            const label = wrapper.querySelector('input[name="mealLabels[]"]');
            const fileInput = wrapper.querySelector('input[type="file"]');
            const previewImage = wrapper.querySelector('img');

            meals.push({
                label: label ? label.value : '',
                description: description ? description.value : '',
                imageData: previewImage && previewImage.src ? previewImage.src : null,
                file: fileInput && fileInput.files && fileInput.files[0] ? fileInput.files[0] : null,
            });
        });

        mealsByDay[canonicalDay] = meals;
    }

    function loadMealsForDay(day) {
        // Limpar refeições atuais, menos o botão de adicionar
        const mealWrappers = mealList.querySelectorAll('[data-meal-item]');
        mealWrappers.forEach(function (wrapper) {
            if (wrapper.id !== 'add-meal-wrapper') {
                wrapper.remove();
            }
        });

        // Se há refeições guardadas para este dia, carregar
        if (mealsByDay[day] && mealsByDay[day].length > 0) {
            mealsByDay[day].forEach(function (mealData, index) {
                appendMealCard(true, mealData);
            });
        } else {
            // Se é a primeira vez neste dia, adicionar 2 refeições vazias
            appendMealCard();
            appendMealCard();
        }
    }

    function appendMealCard(restoreMeal, mealData) {
        if (!template || !template.content) {
            return;
        }

        const fragment = template.content.cloneNode(true);
        const wrapper = fragment.querySelector('[data-meal-item]');
        if (!wrapper) {
            return;
        }

        const mealDayInput = fragment.querySelector('input[name="mealDays[]"]');
        if (mealDayInput) {
            mealDayInput.value = normalizeDay(selectedDayInput.value);
        }

        mealList.insertBefore(fragment, addButton.closest('[id="add-meal-wrapper"]'));
        const allWrappers = mealList.querySelectorAll('[data-meal-item]');
        const newWrapper = allWrappers[allWrappers.length - 1];

        if (newWrapper) {
            // Se está a restaurar dados, preenchê-los
            if (restoreMeal && mealData) {
                const description = newWrapper.querySelector('textarea[name="mealDescriptions[]"]');
                const label = newWrapper.querySelector('input[name="mealLabels[]"]');
                const previewImage = newWrapper.querySelector('img');
                const uploadArea = newWrapper.querySelector('[data-upload-area]');

                if (description) {
                    description.value = mealData.description || '';
                }
                if (label) {
                    label.value = mealData.label || '';
                }
                if (previewImage && mealData.imageData) {
                    previewImage.src = mealData.imageData;
                    previewImage.style.display = 'block';
                    const icon = uploadArea.querySelector('i');
                    if (icon) {
                        icon.style.display = 'none';
                    }
                }
            }

            wireUploadArea(newWrapper);
            wireRemoveButton(newWrapper);
        }

        updateLabels();
    }

    daysSelector.addEventListener('click', function (event) {
        const button = event.target.closest('.dia');
        if (!button) {
            return;
        }

        const currentDay = selectedDayInput.value;
        const selectedDay = button.dataset.day || '2ª';

        if (currentDay === selectedDay) {
            return;
        }

        // Guardar o estado do dia atual antes de mudar
        saveMealsForDay(currentDay);

        // Atualizar botões
        daysSelector.querySelectorAll('.dia').forEach(function (dayButton) {
            dayButton.classList.remove('diaSele');
        });

        button.classList.add('diaSele');
        selectedDayInput.value = selectedDay;

        if (selectedDayLabel) {
            selectedDayLabel.textContent = selectedDay;
        }

        // Carregar refeições do novo dia
        loadMealsForDay(selectedDay);

        if (window.history && window.history.replaceState) {
            const url = new URL(window.location.href);
            url.searchParams.set('selectedDay', selectedDay);
            if (selectedDayInput.name) {
                url.searchParams.set(selectedDayInput.name, selectedDayInput.value);
            }
            window.history.replaceState({}, '', url.toString());
        }
    });

    // Inicializar
    const initialDay = selectedDayInput.value || '2ª';
    loadMealsForDay(initialDay);

    if (addButton) {
        addButton.addEventListener('click', function (e) {
            e.preventDefault();
            appendMealCard();
        });
    }

    // Sincronizar dados de mealsByDay com os inputs antes de submeter
    function syncMealsToForm() {
        // Guardar o dia atual
        saveMealsForDay(selectedDayInput.value);

        // Limpar os inputs de refeições existentes
        mealList.querySelectorAll('[data-meal-item]').forEach(function (wrapper) {
            if (wrapper.id !== 'add-meal-wrapper') {
                wrapper.remove();
            }
        });

        // Reconstruir os inputs com todos os dados de todos os dias
        days.forEach(function (day) {
            if (mealsByDay[day] && Array.isArray(mealsByDay[day])) {
                mealsByDay[day].forEach(function (mealData) {
                    if (!template || !template.content) {
                        return;
                    }

                    const fragment = template.content.cloneNode(true);
                    const wrapper = fragment.querySelector('[data-meal-item]');
                    if (!wrapper) {
                        return;
                    }

                    // Preencher os dados da refeição
                    const description = fragment.querySelector('textarea[name="mealDescriptions[]"]');
                    const label = fragment.querySelector('input[name="mealLabels[]"]');
                    const mealDayInput = fragment.querySelector('input[name="mealDays[]"]');
                    const fileInput = fragment.querySelector('input[type="file"]');

                    if (description) {
                        description.value = mealData.description || '';
                    }
                    if (label) {
                        label.value = mealData.label || '';
                    }
                    if (mealDayInput) {
                        mealDayInput.value = day;
                    }

                    // Se há um ficheiro, recrear na DataTransfer
                    if (mealData.file && fileInput) {
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(mealData.file);
                        fileInput.files = dataTransfer.files;
                    }

                    // Adicionar ao DOM antes do botão de adicionar
                    mealList.insertBefore(fragment, addButton.closest('[id="add-meal-wrapper"]'));
                });
            }
        });
    }

    // Listener no submit do formulário
    const planoForm = document.getElementById('plano-form');
    if (planoForm) {
        planoForm.addEventListener('submit', function (e) {
            syncMealsToForm();
        });
    }
})();
JS;

    $this->registerJs($js, \yii\web\View::POS_END);
    ?>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>