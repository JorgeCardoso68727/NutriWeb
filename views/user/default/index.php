<?php

use app\assets\IndexAsset;
use yii\bootstrap5\Html;
use yii\helpers\Url;

IndexAsset::register($this);
$this->title = 'NutriWeb';
$this->context->layout = false;
$this->beginPage();
?>

	<?= Html::csrfMetaTags() ?>
	<title><?= Html::encode($this->title) ?></title>
	<?php $this->head() ?>
    

<?php $this->beginBody() ?>

<main class="landing-page">
	<div class="top-actions">
		<a class="btn-register" href="<?= Url::to(['/user/register']) ?>">Registrar</a>
		<a class="btn-login" href="<?= Url::to(['/user/login']) ?>">Login</a>
	</div>

</main>

<?php $this->endBody() ?>
<?php $this->endPage() ?>
