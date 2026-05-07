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
        <div class="container create-plan-shell py-5">
            <div class="create-plan-header text-center mb-4 mb-lg-5">
                <h2 class="mb-2 mt-2">Criar plano alimentar</h2>
                <p class="text-muted mb-0">Organiza o plano e escolhe as características na barra lateral.</p>
            </div>

            <?= Html::beginForm(['/plan/criar-plano-semanal'], 'post', ['enctype' => 'multipart/form-data', 'id' => 'plano-inicial-form']) ?>
            <div class="row g-4 align-items-start">
                <div class="col-12 col-lg-7">

                    <div class="mb-4">
                        <label for="nomePlano" class="form-label fw-bold small text-uppercase letter-spaced">Nome do plano</label>
                        <input type="text" id="nomePlano" name="nomePlano" class="texto" placeholder="Ex: Plano para perda de gordura">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase letter-spaced">Imagem de capa</label>

                        <div>
                            <input type="file" id="planoImagem" name="planoImagem" hidden accept="image/*">
                            <label class="upload-area" for="planoImagem">
                                <i class="bi bi-plus-lg"></i>
                                <img src="" alt="Preview da imagem do plano" hidden>
                                <span class="upload-area-hint">Adicionar imagem</span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <button type="submit" class="btn btnSeguinte w-100">
                            Criar plano
                        </button>
                    </div>
                </div>

                <div class="col-12 col-lg-5">
                    <!-- Secção de Tags do Plano -->
                    <div class="tags-sidebar">
                        <div class="tags-sidebar-header">
                            <span class="tags-sidebar-badge">Tags</span>
                            <h3>Características do plano</h3>
                            <p>Seleciona os filtros que melhor descrevem o teu plano alimentar.</p>
                        </div>

                        <div class="tags-container" id="tagsContainer">
                            <?php
                            $tags = \app\models\RecipeTag::find()->orderBy(['name' => SORT_ASC])->all();
                            foreach ($tags as $tag):
                            ?>
                                <label class="tag-pill" for="tag_<?= $tag->id ?>">
                                    <input class="tag-pill-input" type="checkbox" name="planoTags[]"
                                        value="<?= $tag->id ?>" id="tag_<?= $tag->id ?>">
                                    <span class="tag-pill-content">
                                        <strong><?= Html::encode($tag->name) ?></strong>
                                        <small><?= Html::encode($tag->description) ?></small>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?= Html::endForm() ?>
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