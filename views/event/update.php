<?php

use app\assets\RecipeEventAsset;
use app\models\Event;
use app\models\EventCategory;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var Event $model */
/** @var EventCategory[] $categories */

$this->title = 'Editar evento';
RecipeEventAsset::register($this);
$categoryItems = [];
foreach ($categories as $category) {
    $categoryItems[$category->id] = $category->name;
}
?>

<div class="event-form-page container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mb-3">
                <?= Html::a('Voltar', ['/event/view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary event-back-btn']) ?>
            </div>

            <div class="card border-0 shadow-sm event-form-card">
                <div class="card-body p-4 p-md-5 event-form-body">
                    <span class="event-kicker">Editar evento</span>
                    <h1 class="event-title h3 mb-4"><?= Html::encode($this->title) ?></h1>

                    <?php $form = ActiveForm::begin(); ?>
                    <?= $form->field($model, 'title', ['labelOptions' => ['label' => 'Título']])->textInput(['maxlength' => true, 'placeholder' => 'Ex: Workshop de Culinária Saudável', 'class' => 'form-control event-input']) ?>
                    <?= $form->field($model, 'category_id', ['labelOptions' => ['label' => 'Categoria']])->dropDownList($categoryItems, ['prompt' => 'Seleciona uma categoria', 'class' => 'form-select event-input']) ?>
                    <?= $form->field($model, 'location', ['labelOptions' => ['label' => 'Localização']])->textInput(['maxlength' => true, 'placeholder' => 'Ex: Lisboa, Porto, Covilhã...', 'class' => 'form-control event-input location-autocomplete']) ?>
                    <?= $form->field($model, 'start_date', ['labelOptions' => ['label' => 'A partir de']])->textInput(['class' => 'form-control event-input event-datetime']) ?>
                    <?= $form->field($model, 'end_date', ['labelOptions' => ['label' => 'Até']])->textInput(['class' => 'form-control event-input event-datetime']) ?>
                    <?= $form->field($model, 'max_participants', ['labelOptions' => ['label' => 'Número Máximo de Participantes']])->input('number', ['min' => 1, 'placeholder' => 'Ex: 50', 'class' => 'form-control event-input']) ?>
                    <?= $form->field($model, 'description', ['labelOptions' => ['label' => 'Descrição']])->textarea(['rows' => 6, 'placeholder' => 'Descreve o evento em detalhe...', 'class' => 'form-control event-input']) ?>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-success event-submit-btn">Guardar alterações</button>
                        <?= Html::a('Cancelar', ['/event/view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary event-cancel-btn']) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$this->registerJs(
    <<<JS
                    flatpickr('.event-datetime', {
                        enableTime: true,
                        time_24hr: true,
                        dateFormat: 'Y-m-d H:i',
                        minuteIncrement: 5,
                    });
                    JS
);
?>