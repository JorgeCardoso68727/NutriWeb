<?php

use app\assets\ProcurarAsset;
use yii\bootstrap5\Html;
use yii\helpers\Url;

ProcurarAsset::register($this);
$this->title = 'Nutriweb - Procurar';
?>

<div class="search-page">
    <div class="search-header">
        <h4>Procurar utilizadores e posts</h4>
        <p>Escreve um nome, username, titulo ou palavra-chave.</p>
    </div>

    <div class="search-form-card">
        <?= Html::beginForm(['procurar'], 'get', ['class' => 'search-form']) ?>
            <i class="bi bi-search search-icon"></i>
            <?= Html::textInput('q', $term, [
                'class' => 'form-control search-input',
                'placeholder' => 'Ex: ana, receitas, proteina...',
                'autocomplete' => 'off',
            ]) ?>
            <?= Html::submitButton('Pesquisar', ['class' => 'btn search-button']) ?>
        <?= Html::endForm() ?>
    </div>

    <?php if ($term === ''): ?>
        <div class="search-empty">
            <i class="bi bi-compass"></i>
            <p>Inicia uma pesquisa para veres resultados.</p>
        </div>
    <?php else: ?>
        <div class="search-meta">
            <span>
                Resultado para <strong><?= Html::encode($term) ?></strong>
            </span>
            <span>
                <?= Html::encode((string) count($users)) ?> utilizadores • <?= Html::encode((string) count($posts)) ?> posts
            </span>
        </div>

        <div class="search-grid">
            <section class="result-section">
                <h5>Utilizadores</h5>

                <?php if (empty($users)): ?>
                    <div class="empty-card">Nenhum utilizador encontrado.</div>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <?php
                        $photoPath = trim((string) ($user['profile_photo'] ?? ''));
                        $photoUrl = $photoPath !== ''
                            ? Url::to('@web/' . ltrim($photoPath, '/'))
                            : Url::to('@web/Img/Nutriweb Logo.png');

                        $firstName = trim((string) ($user['Frist_Name'] ?? ''));
                        $lastName = trim((string) ($user['Last_Name'] ?? ''));
                        $fullName = trim($firstName . ' ' . $lastName);
                        $username = trim((string) ($user['username'] ?? ''));
                        $displayName = $fullName !== '' ? $fullName : ($username !== '' ? $username : 'Utilizador');
                        $profileUrl = $username !== '' ? Url::to('/' . $username) : '#';
                        $bio = trim((string) ($user['Bio'] ?? ''));
                        ?>
                        <a class="result-card user-card" href="<?= Html::encode($profileUrl) ?>">
                            <img src="<?= Html::encode($photoUrl) ?>" alt="Foto de perfil">
                            <div>
                                <h6><?= Html::encode($displayName) ?></h6>
                                <?php if ($username !== ''): ?>
                                    <p>@<?= Html::encode($username) ?></p>
                                <?php endif; ?>
                                <?php if ($bio !== ''): ?>
                                    <small><?= Html::encode($bio) ?></small>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

            <section class="result-section">
                <h5>Posts</h5>

                <?php if (empty($posts)): ?>
                    <div class="empty-card">Nenhum post encontrado.</div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <?php
                        $imagePath = trim((string) ($post['imagem'] ?? ''));
                        $imageUrl = $imagePath !== ''
                            ? Url::to('@web/' . ltrim($imagePath, '/'))
                            : Url::to('@web/Img/Pato-Com-Arroz-bolohesa.png');

                        $username = trim((string) ($post['username'] ?? ''));
                        $displayName = $username !== '' ? $username : 'Utilizador';
                        $postUrl = Url::to(['post-aberto', 'id' => (int) ($post['id'] ?? 0)]);
                        $content = trim((string) ($post['conteudo'] ?? ''));
                        $preview = mb_substr($content, 0, 120);
                        if (mb_strlen($content) > 120) {
                            $preview .= '...';
                        }
                        ?>
                        <a class="result-card post-card" href="<?= Html::encode($postUrl) ?>">
                            <img src="<?= Html::encode($imageUrl) ?>" alt="Imagem do post">
                            <div>
                                <h6><?= Html::encode((string) ($post['titulo'] ?? 'Sem titulo')) ?></h6>
                                <p>por <?= Html::encode($displayName) ?></p>
                                <?php if ($preview !== ''): ?>
                                    <small><?= Html::encode($preview) ?></small>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </div>
    <?php endif; ?>
</div>
