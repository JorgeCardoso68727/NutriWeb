<?php

use app\assets\InicioAsset;
use yii\bootstrap5\Html;
use yii\helpers\Url;


InicioAsset::register($this);
$this->title = 'Nutriweb - Início';
?>
<div class="body-principal">
    <div class="main-content">
        <h5 class="section-title">Receitas sugeridas por Nutricionistas:</h5>

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
                $avatarUrl = $avatarPath !== ''
                    ? Url::to('@web/' . ltrim($avatarPath, '/'))
                    : Url::to('@web/Img/Nutriweb Logo.png');

                $username = trim((string) ($post['username'] ?? ''));
                $displayName = $username !== '' ? $username : 'Utilizador';
                $profileUrl = $username !== '' ? Url::to('/' . $username) : '#';
                $postUrl = Url::to(['/homepage/post-aberto', 'id' => $postId]);
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
                            data-active-color="<?= Html::encode($likeActiveColor) ?>">
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

        <div class="right-sidebar">
            <h6 class="section-title" style="font-size: 0.9rem;">Nutricionistas sugeridos:</h6>

            <?php if (!empty($nutritionists)): ?>
                <?php foreach ($nutritionists as $nutritionist): ?>
                    <?php
                    $photoPath = trim((string) ($nutritionist['profile_photo'] ?? ''));
                    $photoUrl = $photoPath !== ''
                        ? Url::to('@web/' . ltrim($photoPath, '/'))
                        : Url::to('@web/Img/Nutriweb Logo.png');

                    $firstName = trim((string) ($nutritionist['Frist_Name'] ?? ''));
                    $lastName = trim((string) ($nutritionist['Last_Name'] ?? ''));
                    $fullName = trim($firstName . ' ' . $lastName);

                    $username = trim((string) ($nutritionist['username'] ?? ''));
                    $displayName = $fullName !== '' ? $fullName : ($username !== '' ? $username : 'Nutricionista');
                    $bio = trim((string) ($nutritionist['Bio'] ?? ''));
                    $bioText = $bio !== '' ? $bio : 'Nutricionista disponivel para ajudar no seu plano alimentar.';
                    $phone = trim((string) ($nutritionist['Telefone'] ?? ''));
                    $profileUrl = $username !== '' ? Url::to('/' . $username) : '#';
                    $canContact = $phone !== '';
                    ?>
                    <div class="nutri-card">
                        <?= Html::a(
                            Html::img($photoUrl, ['class' => 'nutri-photo', 'alt' => 'Foto de ' . $displayName]),
                            $profileUrl,
                            ['style' => 'display:inline-flex;']
                        ) ?>
                        <div class="nutri-info">
                            <h6><?= Html::encode($displayName) ?></h6>
                            <p><?= Html::encode($bioText) ?></p>
                            <?php if ($phone !== ''): ?>
                                <p><strong>Telefone:</strong> <?= Html::encode($phone) ?></p>
                            <?php endif; ?>
                            <button
                                class="btn-contactar js-contact-qr"
                                type="button"
                                data-name="<?= Html::encode($displayName) ?>"
                                data-phone="<?= Html::encode($phone) ?>"
                                <?= $canContact ? '' : 'disabled title="Sem telefone disponivel"' ?>
                            >
                                Contactar
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="nutri-card">
                    <div class="nutri-info">
                        <h6>Sem sugestoes de momento</h6>
                        <p>Ainda nao existem nutricionistas para mostrar.</p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="text-center" style="font-size: 0.7rem; font-weight: bold; cursor: pointer; color: #248331;">
                ------------- Ver mais -------------
            </div>
        </div>
    </div>

        <div class="modal fade" id="contactQrModal" tabindex="-1" aria-labelledby="contactQrModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="contactQrModalLabel">Adicionar contacto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <p class="mb-2">Aponta a câmara do telemovel para o QR code.</p>
                        <p class="small text-muted mb-3" id="contactQrMeta"></p>
                        <img id="contactQrImage" alt="QR code do contacto" style="width: 260px; height: 260px; max-width: 100%; border-radius: 10px; border: 1px solid rgba(0,0,0,0.08);">
                    </div>
                </div>
            </div>
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
    const trigger = event.target.closest('.js-contact-qr');
    if (!trigger) {
        return;
    }

    const name = (trigger.dataset.name || '').trim();
    const rawPhone = (trigger.dataset.phone || '').trim();
    if (!rawPhone) {
        return;
    }

    const phone = rawPhone.replace(/\s+/g, '');
    const safeName = name !== '' ? name : 'Nutricionista';

    const vcard = [
        'BEGIN:VCARD',
        'VERSION:3.0',
        'FN:' + safeName,
        'TEL;TYPE=CELL:' + phone,
        'END:VCARD'
    ].join('\n');

    const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=' + encodeURIComponent(vcard);

    const qrImage = document.getElementById('contactQrImage');
    const qrMeta = document.getElementById('contactQrMeta');
    const modalElement = document.getElementById('contactQrModal');
    if (!qrImage || !qrMeta || !modalElement || typeof bootstrap === 'undefined') {
        return;
    }

    qrImage.src = qrUrl;
    qrMeta.textContent = safeName + ' • ' + phone;

    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    modal.show();
});
JS);
    ?>