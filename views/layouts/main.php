<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\helpers\Url;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">

<head>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body class="d-flex flex-column h-100">
    <?php $this->beginBody() ?>

    <div class="sidebar">
        <img src="<?= Url::to('@web/Img/Nutriweb Logo.png') ?>" class="sidebar-logo">

        <nav class="nav flex-column">
            <a class="nav-link" href="<?= yii\helpers\Url::to(['/user/inicio']) ?>"><i class="bi bi-house-door"></i> Início</a>
            <a class="nav-link" href="<?= yii\helpers\Url::to(['/user/feed']) ?>"><i class="bi bi-egg-fried"></i> Feed</a>
            <a class="nav-link" href="<?= yii\helpers\Url::to(['/user/mensagens']) ?>"><i class="bi bi-chat-dots"></i> Mensagem</a>
            <a class="nav-link" href="<?= yii\helpers\Url::to(['/user/gotinha']) ?>"><i class="bi bi-droplet"></i> Lembrete de Água</a>
            <a class="nav-link" href="<?= yii\helpers\Url::to(['/user/procurar']) ?>"><i class="bi bi-search"></i> Procurar</a>

            <div class="fixed-bottom ms-3" style="width: 200px;">
                <a class="nav-link" href="<?= yii\helpers\Url::to(['/user/criarpost']) ?>"><i class="bi bi-plus-lg"></i> Criar Post</a>
                <?php if (!Yii::$app->user->isGuest): ?>
                    <a class="nav-link" href="<?= '/' . Yii::$app->user->identity->username ?>">
                        <i class="bi bi-person-circle"></i> Perfil
                    </a>
                <?php else: ?>
                    <a class="nav-link" href="<?= yii\helpers\Url::to(['/user/login']) ?>">
                        <i class="bi bi-person-circle"></i> Perfil
                    </a>
                <?php endif; ?>
            </div>
        </nav>
    </div>

    <main id="main" class="flex-shrink-0" role="main">
        <div class="<?= !empty($this->params['fullWidth']) ? 'container-fluid px-0' : 'container' ?>">
            <?php if (!empty($this->params['breadcrumbs'])): ?>
                <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
            <?php endif ?>
            <?= Alert::widget() ?>
            <?= $content ?>
        </div>
    </main>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>