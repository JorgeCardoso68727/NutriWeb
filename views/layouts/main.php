<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\db\Query;
use yii\helpers\Url;

AppAsset::register($this);
$isFullWidth = !empty($this->params['fullWidth']);

$showCreatePlanLink = false;
$isAdmin = false;
if (!Yii::$app->user->isGuest) {
    $currentUserId = (int) Yii::$app->user->id;

    $roleSchema = Yii::$app->db->schema->getTableSchema('role', true);
    if ($roleSchema !== null) {
        $selectColumns = [];
        if (isset($roleSchema->columns['can_nutricionista'])) {
            $selectColumns[] = 'r.can_nutricionista';
        }
        if (isset($roleSchema->columns['can_admin'])) {
            $selectColumns[] = 'r.can_admin';
        }

        if (!empty($selectColumns)) {
            $permissionValues = (new Query())
                ->select($selectColumns)
                ->from(['u' => 'user'])
                ->innerJoin(['r' => 'role'], 'r.id = u.role_id')
                ->where(['u.id' => $currentUserId])
                ->one();

            $showCreatePlanLink = (int) ($permissionValues['can_nutricionista'] ?? 0) === 1;
            $isAdmin = (int) ($permissionValues['can_admin'] ?? 0) === 1;
        }
    }
}

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
            <a class="nav-link" href="<?= Url::to(['/inicio']) ?>"><i class="bi bi-house-door"></i> Início</a>
            <a class="nav-link" href="<?= Url::to(['/feed']) ?>"><i class="bi bi-egg-fried"></i> Feed</a>
            <a class="nav-link" href="<?= Url::to(['/mensagens']) ?>"><i class="bi bi-chat-dots"></i> Mensagem</a>
            <a class="nav-link" href="<?= Url::to(['/gotinha']) ?>"><i class="bi bi-droplet"></i> Gotinha</a>
            <a class="nav-link" href="<?= Url::to(['/procurar']) ?>"><i class="bi bi-search"></i> Procurar</a>

            <div class="fixed-bottom ms-3" style="width: 200px;">
                <?php if ($showCreatePlanLink): ?>
                    <a class="nav-link" href="<?= Url::to(['/criar-plano']) ?>"><i class="bi bi-clipboard-plus"></i> Criar Plano</a>
                <?php endif; ?>
                <?php if ($isAdmin): ?>
                    <a class="nav-link" href="<?= Url::to(['/reports/dashboard']) ?>"><i class="bi bi-patch-check"></i> Dashboard</a>
                <?php endif; ?>
                <a class="nav-link" href="<?= Url::to(['/criarpost']) ?>"><i class="bi bi-plus-lg"></i> Criar Post</a>
                <?php if (!Yii::$app->user->isGuest): ?>
                    <a class="nav-link" href="<?= '/' . Yii::$app->user->identity->username ?>">
                        <i class="bi bi-person-circle"></i> Perfil
                    </a>
                <?php else: ?>
                    <a class="nav-link" href="<?= Url::to(['/user/login']) ?>">
                        <i class="bi bi-person-circle"></i> Perfil
                    </a>
                <?php endif; ?>
            </div>
        </nav>
    </div>

    <main id="main" class="flex-shrink-0" role="main">
        <div class="<?= $isFullWidth ? 'container-fluid p-0' : 'container' ?>">
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