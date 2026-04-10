<?php

use app\assets\FeedAsset;
use yii\bootstrap5\Html;
use yii\helpers\Url;


FeedAsset::register($this);
$this->title = 'Nutriweb - Feed';

$this->beginPage();
?>

<?= Html::csrfMetaTags() ?>
    <?php $this->head() ?>
<div class="body-principal">
<div class="main-content">
    <?php $this->beginBody() ?>

        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
                <?php
                $postId = (int) ($post['id'] ?? 0);
                $rawColor = trim((string) ($post['CorPost'] ?? ''));
                $normalizedColor = ltrim($rawColor, '#');
                $isValidColor = preg_match('/^[0-9A-Fa-f]{6}$/', $normalizedColor) === 1;
                $cardColor = $isValidColor ? ('#' . strtoupper($normalizedColor)) : '#D9D9D9';

                $red = hexdec(substr(ltrim($cardColor, '#'), 0, 2));
                $green = hexdec(substr(ltrim($cardColor, '#'), 2, 2));
                $blue = hexdec(substr(ltrim($cardColor, '#'), 4, 2));
                $luminance = (0.299 * $red) + (0.587 * $green) + (0.114 * $blue);
                $textColor = $luminance > 160 ? '#1F2937' : '#F9FAFB';

                $imagePath = trim((string) ($post['imagem'] ?? ''));
                $imageUrl = $imagePath !== ''
                    ? Url::to('@web/' . ltrim($imagePath, '/'))
                    : Url::to('@web/Img/Pato-Com-Arroz-bolohesa.png');

                $avatarPath = trim((string) ($post['profile_photo'] ?? ''));
                if ($avatarPath === '' || strcasecmp($avatarPath, 'img/default.jpeg') === 0) {
                    $avatarUrl = Url::to('@web/Img/default.jpeg');
                } else {
                    $avatarUrl = Url::to('@web/' . ltrim($avatarPath, '/'));
                }

                $username = trim((string) ($post['username'] ?? ''));
                $displayName = $username !== '' ? $username : 'Utilizador';
                $profileUrl = $username !== '' ? Url::to('/' . $username) : '#';
                $postUrl = Url::to(['post-aberto', 'id' => $postId]);
                $hasLiked = !empty($likedPostIds[$postId]);
                $likeCount = (int) ($likeCountByPost[$postId] ?? 0);
                $likeActiveColor = $textColor === '#F9FAFB' ? '#FFFFFF' : '#E11D48';
                $likeColor = $hasLiked
                    ? $likeActiveColor
                    : $textColor;
                ?>
                <div class="post-card" style="background-color: <?= Html::encode($cardColor) ?>;">
                    <div class="post-header">
                        <?= Html::a(
                            Html::img($avatarUrl, ['class' => 'avatar-circle', 'alt' => 'Foto de perfil']),
                            $profileUrl,
                            ['style' => 'display:inline-flex;']
                        ) ?>
                        <?= Html::a(
                            Html::encode($displayName),
                            $profileUrl,
                            [
                                'style' => 'color: ' . $textColor . '; text-decoration: none; font-weight: inherit;'
                            ]
                        ) ?>
                    </div>
                    <?= Html::a(
                        Html::img($imageUrl, ['class' => 'img-placeholder', 'alt' => (string) ($post['titulo'] ?? 'Imagem do post')]),
                        $postUrl,
                        ['style' => 'display:block;']
                    ) ?>
                    <div class="post-footer">
                        <?= Html::a(
                            Html::encode((string) ($post['titulo'] ?? 'Sem título')),
                            $postUrl,
                            [
                                'style' => 'color: ' . $textColor . '; font-size: 0.8rem; text-decoration: none;'
                            ]
                        ) ?>
                        <?= Html::beginForm(
                            ['toggle-like', 'postId' => $postId],
                            'post',
                            ['style' => 'margin:0;', 'class' => 'like-toggle-form']
                        ) ?>
                            <button
                                type="submit"
                                class="btn p-0 d-inline-flex align-items-center gap-1 like-toggle-button"
                                style="border:none; background:transparent; color: <?= Html::encode($likeColor) ?>;"
                                aria-label="<?= $hasLiked ? 'Retirar like' : 'Dar like' ?>"
                                data-liked="<?= $hasLiked ? '1' : '0' ?>"
                                data-inactive-color="<?= Html::encode($textColor) ?>"
                                data-active-color="<?= Html::encode($likeActiveColor) ?>"
                            >
                                <span class="like-count" style="font-size: 0.8rem;"><?= Html::encode((string) $likeCount) ?></span>
                                <i class="bi like-icon <?= $hasLiked ? 'bi-heart-fill' : 'bi-heart' ?> fs-4"></i>
                            </button>
                        <?= Html::endForm() ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="post-card" style="background-color: #D9D9D9;">
                <div class="post-footer">
                    <span style="color: #000000; font-size: 0.8rem;">Ainda não existem posts publicados.</span>
                </div>
            </div>
        <?php endif; ?>

</div>

<?php
$this->registerJs(<<<'JS'
document.addEventListener('submit', async function (event) {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.classList.contains('like-toggle-form')) {
        return;
    }

    event.preventDefault();

    const button = form.querySelector('.like-toggle-button');
    const icon = form.querySelector('.like-icon');
    const countLabel = form.querySelector('.like-count');
    if (!button || !icon || !countLabel) {
        form.submit();
        return;
    }

    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

    button.disabled = true;

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': csrfToken,
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error('Falha no pedido');
        }

        const data = await response.json();
        if (!data || !data.success) {
            throw new Error('Resposta invalida');
        }

        const liked = !!data.liked;
        const inactiveColor = button.dataset.inactiveColor || '#1F2937';
        const activeColor = button.dataset.activeColor || '#E11D48';

        button.dataset.liked = liked ? '1' : '0';
        button.style.color = liked ? activeColor : inactiveColor;
        button.setAttribute('aria-label', liked ? 'Retirar like' : 'Dar like');
        icon.classList.toggle('bi-heart-fill', liked);
        icon.classList.toggle('bi-heart', !liked);
        countLabel.textContent = String(data.likeCount ?? 0);
    } catch (error) {
        form.submit();
    } finally {
        button.disabled = false;
    }
});
JS);
?>

<?php $this->endBody() ?>

<?php $this->endPage() ?>