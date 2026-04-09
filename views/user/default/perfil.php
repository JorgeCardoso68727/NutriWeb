<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\assets\PerfilAsset;

PerfilAsset::register($this);


$identity = Yii::$app->user->identity;
$userForView = $viewUser ?? $identity;
$isOwnProfile = $isOwnProfile ?? true;
$posts = $posts ?? [];
$publicationCount = $publicationCount ?? 0;
$followersCount = $followersCount ?? 0;
$followingCount = $followingCount ?? 0;
$isFollowing = $isFollowing ?? false;
$followersList = $followersList ?? [];
$followingList = $followingList ?? [];
$username = $userForView ? $userForView->username : 'utilizador';
$fullName = trim(($profile->Frist_Name ?? '') . ' ' . ($profile->Last_Name ?? ''));
$bio = $profile->Bio ?? '';
$avatar = !empty($profile->Foto)
    ? Url::to('@web/' . ltrim($profile->Foto, '/'))
    : Url::to('@web/Img/Nutriweb Logo.png');
$this->title = 'Nutriweb - '. $username;
?>

<main class="main-content">
    <div class="container py-5 submain">

        <div class="row align-items-center mb-5">
            <div class="col-md-4 text-center">
                <div class="profile-pic-container mx-auto">
                    <img class="profile-avatar" src="<?= $avatar ?>" alt="Foto de perfil">
                </div>
            </div>

            <div class="col-md-8">
                <div class="d-flex align-items-center mb-3">
                    <h4 class="mb-0 me-3"><?= $username ?></h4>
                    <?php if (!$isOwnProfile): ?>
                        <i class="bi bi-exclamation-triangle reportar" data-bs-toggle="modal" data-bs-target="#staticBackdrop" title="Reportar"></i>
                    <?php endif; ?>
                </div>
                <p class="fw-bold mb-3"><?= $fullName !== '' ? $fullName : 'Sem nome definido' ?></p>

                <div class="d-flex mb-4">
                    <div class="me-4 text-center">
                        <span class="fw-bold d-block"><?= (int) $publicationCount ?></span> publicações
                    </div>
                    <button type="button" class="btn p-0 border-0 bg-transparent me-4 text-center" data-bs-toggle="modal" data-bs-target="#followersModal">
                        <span class="fw-bold d-block"><?= (int) $followersCount ?></span> seguidores
                    </button>
                    <button type="button" class="btn p-0 border-0 bg-transparent text-center" data-bs-toggle="modal" data-bs-target="#followingModal">
                        <span class="fw-bold d-block"><?= (int) $followingCount ?></span> a seguir
                    </button>
                </div>

                <div class="mb-3">
                    <p class="mb-0"><?= $bio !== '' ? $bio : 'Sem biografia.' ?></p>
                </div>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-12 d-flex gap-3 px-4">
                <?php if ($isOwnProfile): ?>
                    <?= Html::a('Editar Perfil', ['/user/editar-perfil'], ['class' => 'btn flex-grow-1 py-2 rounded-3 botao-perfil']) ?>
                <?php else: ?>
                    <?= Html::beginForm(['/user/toggle-follow', 'username' => $username], 'post', ['class' => 'flex-grow-1']) ?>
                    <button type="submit" class="btn w-100 py-2 rounded-3 botao-perfil">
                        <?= $isFollowing ? 'Deixar de seguir' : 'Seguir' ?>
                    </button>
                    <?= Html::endForm() ?>
                    <button class="btn flex-grow-1 py-2 rounded-3 botao-perfil">Mensagem</button>
                <?php endif; ?>
            </div>
        </div>

        <hr class="mb-4">
        <div class="row g-3">
            <?php foreach ($posts as $post): ?>
                <?php
                $imagePath = trim((string) ($post->imagem ?? ''));
                $imageUrl = $imagePath !== ''
                    ? Url::to('@web/' . ltrim($imagePath, '/'))
                    : null;
                $cardTitle = trim((string) ($post->titulo ?? ''));
                $cardTitle = $cardTitle !== '' ? $cardTitle : 'Sem título';
                $cardContent = trim((string) ($post->conteudo ?? ''));
                ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="ratio ratio-1x1 bg-light border rounded-3 overflow-hidden position-relative">
                        <img src="<?= Html::encode($imageUrl) ?>" alt="<?= Html::encode($cardTitle) ?>" class="img-fluid object-fit-cover w-100 h-100">
                        <div class="position-absolute bottom-0 start-0 end-0 p-3" style="background: linear-gradient(180deg, transparent, rgba(0,0,0,.7));">
                            <h6 class="text-white mb-1"><?= Html::encode($cardTitle) ?></h6>
                            <?php if ($cardContent !== ''): ?>
                                <small class="text-white-50"><?= Html::encode(mb_substr($cardContent, 0, 90)) ?><?= mb_strlen($cardContent) > 90 ? '...' : '' ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

    <?php if ($isOwnProfile): ?>
        <div class="menu-top-right" id="btnMenu">
            <i class="bi bi-list"></i>
        </div>

        <aside class="sidebar-right" id="sidebarRight">
            <div class="d-flex gap-3 mb-5 fs-4">
                <i class="bi bi-sun-fill simbolo"></i>
                <i class="bi bi-moon-fill simbolo"></i>
            </div>
            <nav class="nav flex-column gap-3">
                <a href="<?= Url::to(['/user/badge']) ?>" class="text-decoration-none text-dark fw-bold">Sou nutricionista</a>
                <a href="#" class="text-decoration-none text-dark fw-bold">Sou Instituto</a>
                <a href="#" class="text-decoration-none text-dark fw-bold">Sobre nos</a>
                <?= Html::beginForm(['/user/logout'], 'post', ['class' => 'mt-2']) ?>
                <button type="submit" class="text-decoration-none text-dark fw-bold border-0 bg-transparent p-0 text-start">
                    Logout
                </button>
                <?= Html::endForm() ?>
            </nav>
        </aside>
    <?php endif; ?>

    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Reportar Utilizador</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
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
    </div>

    <div class="modal fade" id="followersModal" tabindex="-1" aria-labelledby="followersModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="followersModalLabel">Seguidores</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (empty($followersList)): ?>
                        <p class="mb-0 text-muted">Este perfil ainda nao tem seguidores.</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($followersList as $follower): ?>
                                <?= Html::a('@' . Html::encode($follower['username']), '/' . $follower['username'], ['class' => 'list-group-item list-group-item-action']) ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="followingModal" tabindex="-1" aria-labelledby="followingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="followingModalLabel">A seguir</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (empty($followingList)): ?>
                        <p class="mb-0 text-muted">Este perfil ainda nao segue ninguem.</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($followingList as $following): ?>
                                <?= Html::a('@' . Html::encode($following['username']), '/' . $following['username'], ['class' => 'list-group-item list-group-item-action']) ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($isOwnProfile): ?>
        <script>
            const btnMenu = document.getElementById('btnMenu');
            const sidebarRight = document.getElementById('sidebarRight');

            if (btnMenu && sidebarRight) {
                btnMenu.addEventListener('click', (event) => {
                    event.stopPropagation();
                    sidebarRight.classList.toggle('active');
                });

                document.addEventListener('click', (event) => {
                    const clicouDentroDaSidebar = sidebarRight.contains(event.target);
                    const clicouNoBotao = btnMenu.contains(event.target);

                    if (sidebarRight.classList.contains('active') && !clicouDentroDaSidebar && !clicouNoBotao) {
                        sidebarRight.classList.remove('active');
                    }
                });
            }
        </script>
    <?php endif; ?>
</main>