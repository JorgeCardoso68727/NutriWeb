
<?php

use app\assets\MensagensAsset;
use yii\bootstrap5\Html;
use yii\helpers\Url;

MensagensAsset::register($this);
$this->title = 'Nutriweb - Mensagens';
$this->params['fullWidth'] = true;

$selectedUsername = '';
$selectedDisplayName = '';
$selectedPhotoUrl = '';
$updatesUrl = '';

if ($selectedUser !== null) {
    $selectedUsername = trim((string) ($selectedUser['username'] ?? ''));
    $selectedFirstName = trim((string) ($selectedUser['Frist_Name'] ?? ''));
    $selectedLastName = trim((string) ($selectedUser['Last_Name'] ?? ''));
    $selectedFullName = trim($selectedFirstName . ' ' . $selectedLastName);
    $selectedDisplayName = $selectedFullName !== '' ? $selectedFullName : ($selectedUsername !== '' ? $selectedUsername : 'Utilizador');
    $selectedPhotoPath = trim((string) ($selectedUser['profile_photo'] ?? ''));
    $selectedPhotoUrl = $selectedPhotoPath !== '' ? Url::to('@web/' . ltrim($selectedPhotoPath, '/')) : Url::to('@web/Img/Nutriweb Logo.png');
    $updatesUrl = Url::to(['mensagens-updates', 'with' => $selectedUsername]);
}
?>

<div class="mensagens-page">
    <div class="mensagens-layout">
        <aside class="conversations-col">
            <div class="conversations-head">
                <h5 class="m-0 fw-bold">Conversas</h5>
            </div>

            <?= Html::beginForm(['mensagens'], 'get', ['class' => 'user-search-form']) ?>
            <?= Html::textInput('u', $userSearchTerm, [
                'class' => 'user-search-input',
                'placeholder' => 'Procurar utilizador',
                'autocomplete' => 'off',
            ]) ?>
            <?= Html::submitButton('Procurar', ['class' => 'user-search-btn']) ?>
            <?= Html::endForm() ?>

            <?php if ($userSearchTerm !== ''): ?>
                <div class="conversations-list user-search-results">
                    <?php if (empty($userSearchResults)): ?>
                        <div class="empty-note">Sem resultados para a pesquisa.</div>
                    <?php else: ?>
                        <?php foreach ($userSearchResults as $user): ?>
                            <?php
                            $username = trim((string) ($user['username'] ?? ''));
                            $firstName = trim((string) ($user['Frist_Name'] ?? ''));
                            $lastName = trim((string) ($user['Last_Name'] ?? ''));
                            $fullName = trim($firstName . ' ' . $lastName);
                            $displayName = $fullName !== '' ? $fullName : ($username !== '' ? $username : 'Utilizador');
                            $photoPath = trim((string) ($user['profile_photo'] ?? ''));
                            $photoUrl = $photoPath !== '' ? Url::to('@web/' . ltrim($photoPath, '/')) : Url::to('@web/Img/Nutriweb Logo.png');
                            ?>
                            <a class="conversation-item" href="<?= Html::encode(Url::to(['mensagens', 'with' => $username])) ?>">
                                <img class="avatar-circle" src="<?= Html::encode($photoUrl) ?>" alt="Foto de <?= Html::encode($displayName) ?>">
                                <div class="conversation-main">
                                    <p class="conversation-name"><?= Html::encode($displayName) ?></p>
                                    <p class="conversation-preview">@<?= Html::encode($username) ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="conversations-list">
                <?php if (empty($conversationUsers)): ?>
                    <div class="empty-note">Ainda nao tens conversas. Usa a pesquisa para iniciar uma.</div>
                <?php else: ?>
                    <?php foreach ($conversationUsers as $user): ?>
                        <?php
                        $userId = (int) ($user['id'] ?? 0);
                        $username = trim((string) ($user['username'] ?? ''));
                        $firstName = trim((string) ($user['Frist_Name'] ?? ''));
                        $lastName = trim((string) ($user['Last_Name'] ?? ''));
                        $fullName = trim($firstName . ' ' . $lastName);
                        $displayName = $fullName !== '' ? $fullName : ($username !== '' ? $username : 'Utilizador');
                        $photoPath = trim((string) ($user['profile_photo'] ?? ''));
                        $photoUrl = $photoPath !== '' ? Url::to('@web/' . ltrim($photoPath, '/')) : Url::to('@web/Img/Nutriweb Logo.png');
                        $isActive = $selectedUser !== null && (int) $selectedUser['id'] === $userId;
                        $unreadCount = (int) ($conversationMetaByUserId[$userId]['unread_count'] ?? 0);
                        $lastMessageAt = trim((string) ($conversationMetaByUserId[$userId]['last_message_at'] ?? ''));
                        $lastMessageTime = $lastMessageAt !== '' ? date('H:i', strtotime($lastMessageAt)) : '';
                        
                        // Se há mensagens não lidas, mostrar contagem; senão, mostrar preview da última mensagem
                        if ($unreadCount > 0) {
                            $previewText = ($unreadCount === 1) ? '1 nova mensagem' : $unreadCount . ' novas mensagens';
                        } else {
                            $lastMessagePreview = trim((string) ($conversationMetaByUserId[$userId]['last_message_preview'] ?? ''));
                            if ($lastMessagePreview !== '') {
                                $previewText = mb_substr($lastMessagePreview, 0, 42);
                                if (mb_strlen($lastMessagePreview) > 42) {
                                    $previewText .= '...';
                                }
                            } else {
                                $previewText = 'Sem mensagens ainda';
                            }
                        }
                        ?>
                        <a class="conversation-item <?= $isActive ? 'active' : '' ?>" href="<?= Html::encode(Url::to(['mensagens', 'with' => $username])) ?>">
                            <img class="avatar-circle" src="<?= Html::encode($photoUrl) ?>" alt="Foto de <?= Html::encode($displayName) ?>">
                            <div class="conversation-main">
                                <div class="conversation-top">
                                    <p class="conversation-name"><?= Html::encode($displayName) ?></p>
                                    <?php if ($lastMessageTime !== ''): ?>
                                        <small class="conversation-time"><?= Html::encode($lastMessageTime) ?></small>
                                    <?php endif; ?>
                                </div>
                                <p class="conversation-preview"><?= Html::encode($previewText) ?></p>
                            </div>
                            <?php if ($unreadCount > 0): ?>
                                <span class="unread-badge" data-user-id="<?= Html::encode((string) $userId) ?>"><?= Html::encode((string) $unreadCount) ?></span>
                            <?php else: ?>
                                <span class="unread-badge d-none" data-user-id="<?= Html::encode((string) $userId) ?>">0</span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </aside>

        <section class="chat-view-col">
            <?php if ($selectedUser === null): ?>
                <div class="chat-empty">
                    <div>
                        <h6>Seleciona um contacto</h6>
                        <p>Abre uma conversa a partir da lista ou pesquisa um utilizador.</p>
                    </div>
                </div>
            <?php else: ?>
                <header class="chat-header-m2">
                    <div class="chat-user-head">
                        <img class="avatar-circle" src="<?= Html::encode($selectedPhotoUrl) ?>" alt="Foto de <?= Html::encode($selectedDisplayName) ?>">
                        <span class="fw-bold"><?= Html::encode($selectedDisplayName) ?></span>
                    </div>
                    <i class="bi bi-three-dots-vertical"></i>
                </header>

                <div id="chatMessages" class="chat-scroll" data-current-user-id="<?= Html::encode((string) $currentUserId) ?>" data-updates-url="<?= Html::encode($updatesUrl) ?>">
                    <?php if (empty($messages)): ?>
                        <div class="empty-note">Ainda nao existem mensagens nesta conversa.</div>
                    <?php else: ?>
                        <?php foreach ($messages as $message): ?>
                            <?php
                            $isMine = (int) ($message['sender_id'] ?? 0) === $currentUserId;
                            $timestamp = trim((string) ($message['created_at'] ?? ''));
                            $formattedTime = $timestamp !== '' ? date('H:i', strtotime($timestamp)) : '';
                            $isRead = (int) ($message['lida'] ?? 0) === 1;
                            ?>
                            <div class="msg-row <?= $isMine ? 'mine' : 'theirs' ?>">
                                <div class="msg-bubble <?= $isMine ? 'msg-sent' : 'msg-received' ?>">
                                    <p class="msg-text"><?= Html::encode((string) ($message['conteudo'] ?? '')) ?></p>
                                    <div class="msg-footer">
                                        <?php if ($isMine): ?>
                                            <span class="msg-tick <?= $isRead ? 'read' : 'sent' ?>">
                                                <?php if ($isRead): ?>
                                                    <i class="bi bi-check2-all"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-check"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($formattedTime !== ''): ?>
                                            <span class="msg-time"><?= Html::encode($formattedTime) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <?= Html::beginForm(['mensagens', 'with' => $selectedUsername], 'post', ['class' => 'chat-input-container', 'id' => 'chatSendForm']) ?>
                <?= Html::hiddenInput('target_user_id', (string) ((int) $selectedUser['id'])) ?>
                <div class="custom-input-group">
                    <i class="bi bi-plus-circle"></i>
                    <?= Html::textInput('conteudo', '', [
                        'class' => 'chat-input',
                        'placeholder' => 'Escreve uma mensagem...',
                        'maxlength' => 2000,
                        'autocomplete' => 'off',
                    ]) ?>
                    <?= Html::submitButton('<i class="bi bi-send-fill"></i>', ['class' => 'chat-send-btn', 'encode' => false]) ?>
                </div>
                <?= Html::endForm() ?>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php
$this->registerJs(<<<'JS'
(function () {
    var chat = document.getElementById('chatMessages');
    var sendForm = document.getElementById('chatSendForm');
    if (!chat) {
        return;
    }

    var currentUserId = Number(chat.dataset.currentUserId || 0);
    var updatesUrl = chat.dataset.updatesUrl || '';

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatTimestamp(value) {
        if (!value) {
            return '';
        }

        var date = new Date(value.replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) {
            return '';
        }

        var hours = String(date.getHours()).padStart(2, '0');
        var minutes = String(date.getMinutes()).padStart(2, '0');

        return hours + ':' + minutes;
    }

    function renderMessages(messages) {
        if (!Array.isArray(messages) || messages.length === 0) {
            chat.innerHTML = '<div class="empty-note">Ainda nao existem mensagens nesta conversa.</div>';
            return;
        }

        var html = '';
        messages.forEach(function (message) {
            var isMine = Number(message.sender_id || 0) === currentUserId;
            var rowClass = isMine ? 'msg-row mine' : 'msg-row theirs';
            var bubbleClass = isMine ? 'msg-bubble msg-sent' : 'msg-bubble msg-received';
            var text = escapeHtml(message.conteudo || '');
            var timeLabel = formatTimestamp(message.created_at || '');
            var isRead = Number(message.lida || 0) === 1;
            
            html += '<div class="' + rowClass + '">';
            html += '<div class="' + bubbleClass + '">';
            html += '<p class="msg-text">' + text + '</p>';
            html += '<div class="msg-footer">';
            
            if (isMine) {
                var tickClass = isRead ? 'msg-tick read' : 'msg-tick sent';
                var tickIcon = isRead ? '<i class="bi bi-check2-all"></i>' : '<i class="bi bi-check"></i>';
                html += '<span class="' + tickClass + '">' + tickIcon + '</span>';
            }
            
            if (timeLabel) {
                html += '<span class="msg-time">' + escapeHtml(timeLabel) + '</span>';
            }
            html += '</div>';
            html += '</div></div>';
        });

        chat.innerHTML = html;
    }

    function updateUnreadBadges(metaByUserId) {
        if (!metaByUserId || typeof metaByUserId !== 'object') {
            return;
        }

        var badges = document.querySelectorAll('.unread-badge[data-user-id]');
        badges.forEach(function (badge) {
            var userId = String(badge.dataset.userId || '');
            var info = metaByUserId[userId] || metaByUserId[Number(userId)] || null;
            var count = info ? Number(info.unread_count || 0) : 0;

            if (count > 0) {
                badge.textContent = String(count);
                badge.classList.remove('d-none');
            } else {
                badge.textContent = '0';
                badge.classList.add('d-none');
            }
        });
    }

    async function refreshConversation() {
        if (!updatesUrl) {
            return;
        }

        try {
            var response = await fetch(updatesUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                return;
            }

            var payload = await response.json();
            if (!payload || !payload.success) {
                return;
            }

            renderMessages(payload.messages || []);
            updateUnreadBadges(payload.conversationMetaByUserId || {});
            chat.scrollTop = chat.scrollHeight;
        } catch (error) {
            // Ignore transient polling errors and retry on next interval.
        }
    }

    async function sendMessageWithoutReload(event) {
        if (!sendForm) {
            return;
        }

        event.preventDefault();

        var input = sendForm.querySelector('.chat-input');
        var submitButton = sendForm.querySelector('.chat-send-btn');
        var text = input ? String(input.value || '').trim() : '';
        if (!text) {
            return;
        }

        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
        var formData = new FormData(sendForm);

        if (submitButton) {
            submitButton.disabled = true;
        }

        try {
            var response = await fetch(sendForm.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData,
                credentials: 'same-origin'
            });

            var payload = null;
            try {
                payload = await response.json();
            } catch (jsonError) {
                payload = null;
            }

            if (!response.ok || !payload || !payload.success) {
                throw new Error('Falha ao enviar');
            }

            if (input) {
                input.value = '';
                input.focus();
            }

            await refreshConversation();
        } catch (error) {
            sendForm.submit();
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
            }
        }
    }

    chat.scrollTop = chat.scrollHeight;
    setInterval(refreshConversation, 3000);

    if (sendForm) {
        sendForm.addEventListener('submit', sendMessageWithoutReload);
    }
})();
JS);
?>