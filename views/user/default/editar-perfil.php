<?php

use app\assets\PerfilAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

PerfilAsset::register($this);
$this->title = 'Nutriweb - Editar perfil';

$avatar = !empty($profile->Foto)
    ? Url::to('@web/' . ltrim($profile->Foto, '/'))
    : Url::to('@web/Img/default.jpeg');

$this->title = 'NutriWeb - Editar Perfil';
?>

<main class="main-content">
    <div class="container py-5 submain edit-profile-page">
        <h4 class="fw-bold mb-4">Editar perfil</h4>

        <div class="edit-card shadow-sm mb-4">
            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                <div class="d-flex align-items-center gap-3">
                    <img src="<?= $avatar ?>" class="profile-img-edit rounded-circle border" alt="Avatar" id="avatarPreview">
                    <div>
                        <p class="mb-0 fw-bold">@<?= Html::encode($user->username) ?></p>
                        <p class="mb-0 text-muted small"><?= Html::encode(trim(($profile->Frist_Name ?? '') . ' ' . ($profile->Last_Name ?? ''))) ?></p>

                    </div>
                </div>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" id="btnAlterarFoto">Alterar foto</button>
            </div>
        </div>

        <?php $form = ActiveForm::begin(['id' => 'edit-profile-form', 'options' => ['enctype' => 'multipart/form-data']]); ?>
        <input type="file" name="profilePhoto" id="profilePhotoInput" class="d-none" accept="image/png,image/jpeg,image/jpg,image/webp">
        <div class="mb-4">
            <label class="form-label fw-bold small">Nome</label>
            <?= $form->field($profile, 'Frist_Name')->textInput([
                'class' => 'form-control custom-input shadow-sm',
                'placeholder' => 'Primeiro nome',
            ])->label(false) ?>
            <div class="form-text small">Podes apenas alterar o teu nome duas vezes num prazo de 14 dias.</div>
        </div>

        <div class="mb-4">
            <label class="form-label fw-bold small">Apelido</label>
            <?= $form->field($profile, 'Last_Name')->textInput([
                'class' => 'form-control custom-input shadow-sm',
                'placeholder' => 'Apelido',
            ])->label(false) ?>
        </div>

        <div class="mb-4">
            <label class="form-label fw-bold small">Nome de utilizador</label>
            <input type="text" class="form-control custom-input shadow-sm" value="<?= Html::encode($user->username) ?>" disabled>
        </div>

        <div class="mb-4">
            <label class="form-label fw-bold small">Bio</label>
            <?= $form->field($profile, 'Bio')->textarea([
                'class' => 'form-control custom-input shadow-sm',
                'rows' => 3,
                'maxlength' => 150,
                'id' => 'bioInput',
                'placeholder' => 'Escreve algo sobre ti',
            ])->label(false) ?>
            <div class="text-end small text-muted"><span id="bioCount"><?= strlen((string)($profile->Bio ?? '')) ?></span> / 150</div>
        </div>


        <div class="text-center mt-5 d-flex justify-content-center gap-3 flex-wrap">
            <?= Html::a('Cancelar', '/' . $user->username, ['class' => 'btn btn-outline-secondary px-5 py-2 fw-bold rounded-pill']) ?>
            <?= Html::submitButton('Guardar Alteracoes', ['class' => 'btn btn-save px-5 py-2 fw-bold rounded-pill shadow']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</main>

<script>
    (function() {
        const bioInput = document.getElementById('bioInput');
        const bioCount = document.getElementById('bioCount');
        const btnAlterarFoto = document.getElementById('btnAlterarFoto');
        const profilePhotoInput = document.getElementById('profilePhotoInput');
        const selectedFileName = document.getElementById('selectedFileName');
        const avatarPreview = document.getElementById('avatarPreview');
        const fotoPathPreview = document.getElementById('fotoPathPreview');

        if (!bioInput || !bioCount) {
            return;
        }

        const updateCount = () => {
            bioCount.textContent = bioInput.value.length;
        };

        if (btnAlterarFoto && profilePhotoInput) {
            btnAlterarFoto.addEventListener('click', () => {
                profilePhotoInput.click();
            });

            profilePhotoInput.addEventListener('change', () => {
                const selectedFile = profilePhotoInput.files && profilePhotoInput.files[0] ? profilePhotoInput.files[0] : null;
                if (!selectedFile) {
                    return;
                }

                if (selectedFileName) {
                    selectedFileName.value = selectedFile.name;
                }

                if (fotoPathPreview) {
                    fotoPathPreview.textContent = selectedFile.name;
                }

                if (avatarPreview) {
                    avatarPreview.src = URL.createObjectURL(selectedFile);
                }
            });
        }

        bioInput.addEventListener('input', updateCount);
        updateCount();
    })();
</script>