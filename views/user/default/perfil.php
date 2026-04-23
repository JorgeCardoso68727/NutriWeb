<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\assets\PerfilAsset;

PerfilAsset::register($this);
$this->params['fullWidth'] = true;

$isOwnProfile = (bool) $isOwnProfile;
$isFollowing = (bool) $isFollowing;
$isNutritionistProfile = (bool) $isNutritionistProfile;
$isAdminViewer = (bool) ($isAdminViewer ?? false);
$isViewedUserAdmin = (bool) ($isViewedUserAdmin ?? false);
$isReviewMode = (bool) ($isReviewMode ?? false);
$canModerateThisAccount = $isAdminViewer && !$isOwnProfile && ($isReviewMode || $isViewedUserAdmin || $isNutritionistProfile);
$plans = $plans ?? [];
$avatar = Url::to($avatarPath);


$renderPostsGrid = static function (array $posts, string $columnClass, string $ratioClass): void {
    if (empty($posts)) {
        echo '<div class="col-12 text-center text-muted py-4">Sem publicações ainda.</div>';
        return;
    }

    foreach ($posts as $post) {
        $postId = (int) ($post->id ?? 0);
        $postUrl = Url::to(['/homepage/post-aberto', 'id' => $postId]);
        $imagePath = trim((string) ($post->imagem ?? ''));
        $imageUrl = $imagePath !== ''
            ? Url::to('@web/' . ltrim($imagePath, '/'))
            : null;
        $cardTitle = trim((string) ($post->titulo ?? ''));
        $cardTitle = $cardTitle !== '' ? $cardTitle : 'Post';

        echo '<div class="' . Html::encode($columnClass) . '">';
        echo '<a href="' . Html::encode($postUrl) . '" class="d-block text-decoration-none">';
        echo '<div class="' . Html::encode($ratioClass) . '">';

        if ($imageUrl !== null) {
            echo '<img src="' . Html::encode($imageUrl) . '" alt="' . Html::encode($cardTitle) . '" class="img-fluid object-fit-cover w-100 h-100">';
        } else {
            echo '<div class="d-flex align-items-center justify-content-center text-muted w-100 h-100">';
            echo '<i class="bi bi-image fs-3"></i>';
            echo '</div>';
        }

        echo '</div>';
        echo '</a>';
        echo '</div>';
    }
};
?>

<main class="main-content">
    <div class="container py-5 submain">

        <?php if (Yii::$app->session->hasFlash('Plan-success')): ?>
            <div class="alert alert-success mb-4"><?= Html::encode(Yii::$app->session->getFlash('Plan-success')) ?></div>
        <?php endif; ?>
        <?php if (Yii::$app->session->hasFlash('Plan-error')): ?>
            <div class="alert alert-danger mb-4"><?= Html::encode(Yii::$app->session->getFlash('Plan-error')) ?></div>
        <?php endif; ?>
        <?php if (Yii::$app->session->hasFlash('Profile-success')): ?>
            <div class="alert alert-success mb-4"><?= Html::encode(Yii::$app->session->getFlash('Profile-success')) ?></div>
        <?php endif; ?>
        <?php if (Yii::$app->session->hasFlash('Profile-error')): ?>
            <div class="alert alert-danger mb-4"><?= Html::encode(Yii::$app->session->getFlash('Profile-error')) ?></div>
        <?php endif; ?>

        <div class="row align-items-center mb-5">
            <div class="col-md-4 text-center">
                <div class="profile-pic-container mx-auto">
                    <img class="profile-avatar" src="<?= $avatar ?>" alt="Foto de perfil">
                </div>
            </div>

            <div class="col-md-8">
                <div class="d-flex align-items-center mb-3">
                    <h4 class="mb-0 me-3"><?= Html::encode($displayName) ?></h4>
                    <?php if ($isViewedUserAdmin): ?>
                        <span class="badge rounded-pill text-bg-dark me-2" title="Conta Admin">
                            <i class="bi bi-shield-lock-fill me-1"></i>Admin
                        </span>
                    <?php endif; ?>
                    <?php if ($isNutritionistProfile): ?>
                        <i class="bi bi-patch-check-fill text-success fs-5" title="Perfil Verificado"></i>
                    <?php endif; ?>
                    <?php if (!$isOwnProfile && !$canModerateThisAccount): ?>
                        <i class="bi bi-exclamation-triangle reportar ms-5" data-bs-toggle="modal" data-bs-target="#staticBackdrop" title="Reportar"></i>
                    <?php endif; ?>
                    <?php if ($canModerateThisAccount): ?>
                        <i class="bi bi-hammer ms-3" data-bs-toggle="modal" data-bs-target="#moderateAccountModal" title="Moderar conta" style="font-size: 1.2rem; color: #c94f4f; cursor: pointer;"></i>
                    <?php endif; ?>
                </div>
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
                    <p class="mb-0"><?= Html::encode($bio !== '' ? $bio : 'Sem biografia.') ?></p>
                </div>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-12 d-flex gap-3 px-4">
                <?php if ($isOwnProfile): ?>
                    <?= Html::a('Editar Perfil', ['/editar-perfil'], ['class' => 'btn flex-grow-1 py-2 rounded-3 botao-perfil']) ?>
                <?php else: ?>
                    <?= Html::beginForm(['/profile/toggle-follow', 'username' => $username], 'post', ['class' => 'flex-grow-1']) ?>
                    <button type="submit" class="btn w-100 py-2 rounded-3 botao-perfil">
                        <?= $isFollowing ? 'Deixar de seguir' : 'Seguir' ?>
                    </button>
                    <?= Html::endForm() ?>
                    <button class="btn flex-grow-1 py-2 rounded-3 botao-perfil">Mensagem</button>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($isNutritionistProfile): ?>
            <ul class="nav nav-tabs justify-content-center border-0 mb-4" id="tabela" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active border-0 bg-transparent text-dark fw-bold" data-bs-toggle="tab" data-bs-target="#posts" type="button" role="tab" aria-controls="posts" aria-selected="true"><i class="bi bi-grid-3x3 me-2"></i>Posts</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link border-0 bg-transparent text-muted fw-bold" data-bs-toggle="tab" data-bs-target="#plans" type="button" role="tab" aria-controls="plans" aria-selected="false"><i class="bi bi-egg-fried me-2"></i>Planos</button>
                </li>
            </ul>

            <div class="tab-content" id="conteudotabela">
                <div class="tab-pane fade show active" id="posts" role="tabpanel">
                    <div class="row g-1">
                        <?php $renderPostsGrid($posts, 'col-4', 'ratio ratio-1x1 bg-light border overflow-hidden rounded-2'); ?>
                    </div>
                </div>
                <div class="tab-pane fade" id="plans" role="tabpanel">

                    <?php if (empty($plans)): ?>
                        <div class="text-center text-muted py-4">Ainda nao existem planos para mostrar.</div>
                    <?php else: ?>
                        <div class="row g-1">
                            <?php foreach ($plans as $plan): ?>
                                <?php
                                $planId = (int) ($plan->id ?? 0);
                                $planUrl = Url::to(['/plan/ver-plano', 'id' => $planId]);
                                $planStructure = json_decode((string) ($plan->estrutura_json ?? ''), true);
                                $planImagePath = trim((string) ($planStructure['imagemPlano'] ?? ''));
                                $planImageUrl = $planImagePath !== ''
                                    ? Url::to('@web/' . ltrim($planImagePath, '/'))
                                    : null;
                                $planTitle = trim((string) ($plan->titulo ?? ''));
                                $planTitle = $planTitle !== '' ? $planTitle : 'Plano nutricional';
                                ?>
                                <div class="col-4">
                                    <a href="<?= Html::encode($planUrl) ?>" class="d-block text-decoration-none">
                                        <div class="ratio ratio-1x1 bg-light border overflow-hidden rounded-2">
                                            <?php if ($planImageUrl !== null): ?>
                                                <img src="<?= Html::encode($planImageUrl) ?>" alt="<?= Html::encode($planTitle) ?>" class="img-fluid object-fit-cover w-100 h-100">
                                            <?php else: ?>
                                                <div class="d-flex align-items-center justify-content-center text-muted small px-2 text-center">
                                                    <?= Html::encode($planTitle) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <hr class="mb-4">
            <div class="row g-3">
                <?php $renderPostsGrid($posts, 'col-12 col-md-6 col-lg-4', 'ratio ratio-1x1 bg-light border rounded-3 overflow-hidden'); ?>
            </div>
        <?php endif; ?>
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
                <a href="<?= Url::to(['/badge']) ?>" class="text-decoration-none text-dark fw-bold">Sou nutricionista</a>
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
                    <h1 class="modal-title fs-5" id="staticBackdropLabel"><?= $isNutritionistProfile ? 'Reportar Profissional' : 'Reportar Utilizador' ?></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?= Html::beginForm(['/reportar'], 'post', ['id' => 'profile-report-form']) ?>
                    <?= Html::hiddenInput('target_type', 'profile') ?>
                    <?= Html::hiddenInput('target_user_id', (int) ($viewUser->id ?? 0)) ?>
                    <?= Html::hiddenInput('target_post_id', '') ?>
                    <div class="mb-3">
                        <label for="motivo" class="form-label">Motivo do Reporte</label>
                        <select class="form-select" id="motivo" name="motivo" required>
                            <option value="">Seleciona um motivo...</option>
                            <?php if ($isNutritionistProfile): ?>
                                <option value="ma-conduta">Má conduta profissional</option>
                                <option value="informacao-falsa">Informação médica falsa</option>
                                <option value="spam">Spam</option>
                            <?php else: ?>
                                <option value="conteudo-inapropriado">Conteúdo Inapropriado</option>
                                <option value="assedio">Assédio</option>
                                <option value="spam">Spam</option>
                                <option value="fraude">Fraude</option>
                                <option value="outro">Outro</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3" placeholder="Descreve o motivo do teu reporte..."></textarea>
                    </div>
                    <?= Html::endForm() ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success me-auto" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" form="profile-report-form">Reportar</button>
                </div>
            </div>
        </div>
    </div>

    <?php if ($canModerateThisAccount): ?>
        <div class="modal fade" id="moderateAccountModal" tabindex="-1" aria-labelledby="moderateAccountModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="moderateAccountModalLabel">Moderar conta</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">Seleciona a acao para esta conta.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary me-auto" data-bs-dismiss="modal">Cancelar</button>

                        <?= Html::beginForm(['/moderar-conta', 'id' => (int) ($viewUser->id ?? 0), 'acao' => 'nao-banir'], 'post', ['class' => 'd-inline']) ?>
                        <button type="submit" class="btn btn-outline-secondary">Nao banir</button>
                        <?= Html::endForm() ?>

                        <?= Html::beginForm(['/moderar-conta', 'id' => (int) ($viewUser->id ?? 0), 'acao' => 'banir'], 'post', ['class' => 'd-inline']) ?>
                        <button type="submit" class="btn btn-danger" data-confirm="Tens a certeza que queres banir esta conta?">Banir conta</button>
                        <?= Html::endForm() ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

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