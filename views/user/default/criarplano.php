<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'NutriWeb - Criar Plano Alimentar';
$profileHref = !Yii::$app->user->isGuest ? Url::to('/' . Yii::$app->user->identity->username) : Url::to(['/perfil']);

$this->beginPage();
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NutriWeb - Criar Plano Alimentar</title>
    <?= Html::csrfMetaTags() ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= Html::encode(Url::to('@web/css/planoalimentar.css')) ?>">
    <?php $this->head() ?>
</head>

<body class="body-Principal">
    <?php $this->beginBody() ?>

    <div class="main-layout">
        <div class="container" style="max-width: 500px;">
            <h2 class="text-center mb-5 mt-5">Criar plano Alimentar</h2>

            <?= Html::beginForm(['/plan/criar-plano-semanal'], 'post', ['enctype' => 'multipart/form-data', 'id' => 'plano-inicial-form']) ?>
            <div class="mb-4">
                <label for="nomePlano" class="form-label fw-bold small">Insira o nome do Plano Nutricional</label><br>
                <input type="text" id="nomePlano" name="nomePlano" class="texto">
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold small">Insira a Imagem do Plano Nutricional</label>

                <div>
                    <input type="file" id="planoImagem" name="planoImagem" hidden accept="image/*">
                    <label class="upload-area" for="planoImagem">
                        <i class="bi bi-plus-lg"></i>
                        <img src="" alt="Preview da imagem do plano" hidden>
                    </label>
                </div>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btnSeguinte">
                    Seguinte
                </button>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>

    <?php
    $js = <<<'JS'
(function () {
    const fileInput = document.getElementById('planoImagem');
    const previewImage = document.querySelector('.upload-area img');
    const uploadIcon = document.querySelector('.upload-area i');

    if (!fileInput || !previewImage) {
        return;
    }

    fileInput.addEventListener('change', function (event) {
        const file = event.target.files && event.target.files[0];
        if (!file) {
            previewImage.hidden = true;
            previewImage.src = '';
            if (uploadIcon) {
                uploadIcon.style.display = '';
            }
            return;
        }

        const reader = new FileReader();
        reader.onload = function (readerEvent) {
            previewImage.src = String(readerEvent.target.result || '');
            previewImage.hidden = false;
            if (uploadIcon) {
                uploadIcon.style.display = 'none';
            }
        };
        reader.readAsDataURL(file);
    });
})();
JS;

    $this->registerJs($js, \yii\web\View::POS_END);
    ?>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>