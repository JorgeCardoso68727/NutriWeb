<?php

use app\assets\PostAbertoAsset;
use yii\bootstrap5\Html;
use yii\helpers\Url;


PostAbertoAsset ::register($this);
$this->title = 'Nutriweb - '. (string) ($post['titulo'] ?? 'Post');

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
$avatarUrl = $avatarPath !== ''
    ? Url::to('@web/' . ltrim($avatarPath, '/'))
    : Url::to('@web/Img/default.jpeg');

$username = trim((string) ($post['username'] ?? ''));
$displayName = $username !== '' ? $username : 'Utilizador';
$profileUrl = $username !== '' ? Url::to('/' . $username) : '#';
$content = trim((string) ($post['conteudo'] ?? ''));
$likeActiveColor = $textColor === '#F9FAFB' ? '#FFFFFF' : '#E11D48';
$likeColor = $hasLiked ? $likeActiveColor : $textColor;

$this->beginPage();
?>

<?= Html::csrfMetaTags() ?>
    <?php $this->head() ?>
<div class="body-principal">
<div class="main-content">
    <?php $this->beginBody() ?>
    <div style="width: 100%; margin-bottom: 12px;">
        <button
            type="button"
            id="back-to-inicio"
            class="btn p-0"
            style="text-decoration:none; color:#248331; font-weight:600;"
            data-inicio-url="<?= Html::encode(Url::to(['inicio'])) ?>"
        >Voltar</button>
    </div>
    <div class="post-card" style="background-color: <?= Html::encode($cardColor) ?>; ">
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
            <?php if (!empty($canRemove)): ?>
                <?= Html::beginForm(['remove-post', 'id' => $postId], 'post', ['class' => 'ms-auto', 'style' => 'margin:0;']) ?>
                    <button
                        type="submit"
                        class="btn p-0 d-inline-flex align-items-center"
                        style="border:none; background:transparent; color: <?= Html::encode($textColor) ?>;"
                        data-confirm="Tens a certeza que queres remover este post?"
                        aria-label="Remover post"
                    >
                        <i class="bi bi-trash fs-4"></i>
                    </button>
                <?= Html::endForm() ?>
            <?php elseif (!empty($canReport)): ?>
                <button
                    type="button"
                    id="report-post-button"
                    class="btn p-0 ms-auto d-inline-flex align-items-center"
                    style="border:none; background:transparent; color: <?= Html::encode($textColor) ?>;"
                    data-bs-toggle="modal"
                    data-bs-target="#staticBackdrop"
                    aria-label="Reportar post"
                >
                    <i class="bi bi-exclamation-triangle fs-4"></i>
                </button>
            <?php endif; ?>
        </div>

        <?= Html::img($imageUrl, ['class' => 'img-placeholder', 'alt' => (string) ($post['titulo'] ?? 'Imagem do post')]) ?>

        <div class="post-footer" style="align-items:flex-start; flex-direction:column; gap:8px;">
            <div style="width:100%; display:flex; align-items:center; justify-content:space-between; gap:10px;">
                <h5 style="margin:0; color: <?= Html::encode($textColor) ?>;"><?= Html::encode((string) ($post['titulo'] ?? 'Sem titulo')) ?></h5>
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
            <?php if ($content !== ''): ?>
                <p style="margin:0; color: <?= Html::encode($textColor) ?>; white-space: pre-wrap;"><?= Html::encode($content) ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>

<?php if (!empty($canReport)): ?>
<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Reportar Post</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body ">
                <form>
                <div class="mb-3">
                    <label for="motivo" class="form-label">Motivo do Reporte</label>
                    <select class="form-select" id="motivo" required>
                    <option value="">Seleciona um motivo...</option>
                    <option value="conteudo-inapropriado">Conteúdo Inapropriado</option>
                    <option value="assedio">Assédio</option>
                    <option value="spam">Spam</option>
                    <option value="fraude">Fraude</option>
                    <option value="outro">Outro</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="descricao" class="form-label">Descrição</label>
                    <textarea class="form-control" id="descricao" rows="3" placeholder="Descreve o motivo do teu reporte..."></textarea>
                </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success me-auto" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger">Reportar</button>
            </div>
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

document.addEventListener('click', function (event) {
    const trigger = event.target.closest('#report-post-button');
    if (!trigger) {
        return;
    }

    const modalElement = document.getElementById('staticBackdrop');
    if (!modalElement || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
        return;
    }

    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    modal.show();
});

document.addEventListener('click', function (event) {
    const backButton = event.target.closest('#back-to-inicio');
    if (!backButton) {
        return;
    }

    if (window.history.length > 1) {
        window.history.back();
        return;
    }

    window.location.href = backButton.dataset.inicioUrl || '/user/inicio';
});
JS);
?>

<?php $this->endBody() ?>

<?php $this->endPage() ?>