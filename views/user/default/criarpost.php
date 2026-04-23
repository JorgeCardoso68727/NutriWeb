<?php
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use app\assets\CriarPostAsset;

CriarPostAsset::register($this);
$this->title = 'Nutriweb - Criar Post';
$this->beginPage();
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <?= Html::csrfMetaTags() ?>
    <title>Criar Post</title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<?php if ($flash = Yii::$app->session->getFlash('Post-success')): ?>
    <div class="alert alert-success" style="position: fixed; top: 20px; right: 20px; z-index: 1000;"><?= Html::encode($flash) ?></div>
<?php endif; ?>

<?php if ($flashError = Yii::$app->session->getFlash('Post-error')): ?>
    <div class="alert alert-danger" style="position: fixed; top: 20px; right: 20px; z-index: 1000;"><?= Html::encode($flashError) ?></div>
<?php endif; ?>

    <?php $form = ActiveForm::begin([
        'id' => 'criar-post-form',
        'options' => [
            'enctype' => 'multipart/form-data',
            'class' => 'main-layout' // O Form agora é o container Flex
        ]
    ]); ?>

        <div class="editor-area">
            <div class="top-bar">
                <?= Html::submitButton('Publicar', ['class' => 'btn-publish']) ?>
            </div>

            <div class="post-card-preview" id="main-preview-card">
                <div class="image-upload-area" id="trigger-upload">
                    <i class="bi bi-plus-lg" id="upload-icon"></i>
                    <img src="" id="image-preview" style="display:none;">
                    
                    <?= $form->field($post, 'imagem', ['template' => '{input}{error}'])->fileInput([
                        'id' => 'file-input',
                        'hidden' => true,
                        'accept' => 'image/*'
                    ])->label(false) ?>
                </div>

                <div class="crop-hint" id="crop-hint" style="display:none;">Arrasta a imagem para ajustar o corte antes de publicar.</div>

                <?= $form->field($post, 'titulo', ['template' => '{input}{error}'])->textInput([
                    'class' => 'post-titlearea',
                    'placeholder' => 'Insira o título...',
                ])->label(false) ?>

                <?= $form->field($post, 'conteudo', ['template' => '{input}{error}'])->textarea([
                    'class' => 'post-textarea',
                    'placeholder' => 'Insira a descrição...',
                ])->label(false) ?>

                <?= $form->field($post, 'CorPost', ['template' => '{input}'])->hiddenInput()->label(false) ?>
            </div>
        </div>

        <div class="tools-area">
            <div class="color-picker-container">
                <div class="color-grid">
                    <?php 
                    $colors = [ '#D9D9D9', '#F7A86F', '#F4C56A', '#F7D95A', '#B9E38A', '#8FDDB1', '#7CCFD4', '#8FB3FF', '#B29AFF', '#D79AFF', '#FF8FA8', '#FFB77A', '#FFE46A', '#A9D6FF', '#B8C0FF', '#F59AA8', '#B7DB6E', '#D9A6F2', '#FFC9D4', '#F28BA8' ];   
                    foreach ($colors as $color): ?>
                        <div class="color-swatch" style="background: <?= $color ?>;" data-color="<?= $color ?>"></div>
                    <?php endforeach; ?>
                </div>

                <div style="margin-top: 12px; margin-bottom: 10px;">
                    <label for="native-color-picker" style="display:block; font-size: 0.85rem; margin-bottom: 6px;">Escolher cor personalizada:</label>
                    <input type="color" id="native-color-picker" value="#D9D9D9" style="width: 100%; height: 38px; border: none; padding: 0; background: transparent; cursor: pointer;">
                </div>
            </div>
        </div>

    <?php ActiveForm::end(); ?>

<?php
$js = <<<JS
    (function () {
        const form = document.getElementById('criar-post-form');
        const triggerUpload = document.getElementById('trigger-upload');
        const fileInput = document.getElementById('file-input');
        const imagePreview = document.getElementById('image-preview');
        const uploadIcon = document.getElementById('upload-icon');
        const previewCard = document.getElementById('main-preview-card');
        const cropHint = document.getElementById('crop-hint');
        const nativeColorPicker = document.getElementById('native-color-picker');
        const postColorInput = document.querySelector('input[name="Post[CorPost]"]') || document.getElementById('post-corpost');
        const toolsArea = document.querySelector('.tools-area');

        const cropState = {
            file: null,
            naturalWidth: 0,
            naturalHeight: 0,
            scale: 1,
            offsetX: 0,
            offsetY: 0,
            isDragging: false,
            dragStartX: 0,
            dragStartY: 0,
            dragOriginX: 0,
            dragOriginY: 0,
            isSubmitting: false,
        };

        if (!previewCard || !toolsArea) {
            return;
        }

        function clamp(value, min, max) {
            return Math.min(Math.max(value, min), max);
        }

        function getFrameSize() {
            const rect = triggerUpload ? triggerUpload.getBoundingClientRect() : imagePreview.getBoundingClientRect();
            return {
                width: Math.max(1, rect.width),
                height: Math.max(1, rect.height),
            };
        }

        function updateImagePreview() {
            if (!imagePreview || imagePreview.style.display === 'none' || !cropState.file) {
                return;
            }

            const frame = getFrameSize();
            const baseScale = Math.max(frame.width / cropState.naturalWidth, frame.height / cropState.naturalHeight);
            const scale = baseScale * cropState.scale;
            const drawWidth = cropState.naturalWidth * scale;
            const drawHeight = cropState.naturalHeight * scale;
            const overflowX = Math.max(0, drawWidth - frame.width);
            const overflowY = Math.max(0, drawHeight - frame.height);

            cropState.offsetX = clamp(cropState.offsetX, -overflowX / 2, overflowX / 2);
            cropState.offsetY = clamp(cropState.offsetY, -overflowY / 2, overflowY / 2);

            const left = (frame.width - drawWidth) / 2 + cropState.offsetX;
            const top = (frame.height - drawHeight) / 2 + cropState.offsetY;

            imagePreview.style.display = 'block';
            imagePreview.style.width = drawWidth + 'px';
            imagePreview.style.height = drawHeight + 'px';
            imagePreview.style.left = left + 'px';
            imagePreview.style.top = top + 'px';
            imagePreview.style.cursor = cropState.isDragging ? 'grabbing' : 'grab';

            if (uploadIcon) {
                uploadIcon.style.display = 'none';
            }

            if (cropHint) {
                cropHint.style.display = 'block';
            }
        }

        function resetCropState() {
            cropState.scale = 1;
            cropState.offsetX = 0;
            cropState.offsetY = 0;
            cropState.isDragging = false;
            cropState.dragStartX = 0;
            cropState.dragStartY = 0;
            cropState.dragOriginX = 0;
            cropState.dragOriginY = 0;
        }

        function prepareCroppedFile() {
            return new Promise(function (resolve, reject) {
                if (!imagePreview || !cropState.file || !cropState.naturalWidth || !cropState.naturalHeight) {
                    resolve();
                    return;
                }

                const frame = getFrameSize();
                const baseScale = Math.max(frame.width / cropState.naturalWidth, frame.height / cropState.naturalHeight);
                const scale = baseScale * cropState.scale;
                const drawWidth = cropState.naturalWidth * scale;
                const drawHeight = cropState.naturalHeight * scale;
                const left = (frame.width - drawWidth) / 2 + cropState.offsetX;
                const top = (frame.height - drawHeight) / 2 + cropState.offsetY;
                const sourceX = (-left) / scale;
                const sourceY = (-top) / scale;
                const sourceWidth = frame.width / scale;
                const sourceHeight = frame.height / scale;
                const canvas = document.createElement('canvas');
                const outputWidth = Math.round(frame.width);
                const outputHeight = Math.round(frame.height);
                const context = canvas.getContext('2d');

                if (!context) {
                    resolve();
                    return;
                }

                canvas.width = outputWidth;
                canvas.height = outputHeight;

                try {
                    context.drawImage(imagePreview, sourceX, sourceY, sourceWidth, sourceHeight, 0, 0, outputWidth, outputHeight);
                } catch (error) {
                    reject(error);
                    return;
                }

                const mimeType = cropState.file.type && cropState.file.type.indexOf('image/') === 0 ? cropState.file.type : 'image/jpeg';
                const finalMimeType = mimeType === 'image/jpg' ? 'image/jpeg' : mimeType;

                canvas.toBlob(function (blob) {
                    if (!blob) {
                        resolve();
                        return;
                    }

                    const extension = finalMimeType === 'image/png' ? 'png' : finalMimeType === 'image/webp' ? 'webp' : 'jpg';
                    const baseName = cropState.file.name.replace(/\.[^.]+$/, '');
                    const croppedFile = new File([blob], baseName + '-crop.' + extension, {
                        type: finalMimeType,
                        lastModified: Date.now(),
                    });
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(croppedFile);
                    fileInput.files = dataTransfer.files;
                    resolve();
                }, finalMimeType, finalMimeType === 'image/png' ? undefined : 0.92);
            });
        }

        function startDrag(event) {
            if (!cropState.file) {
                return;
            }

            cropState.isDragging = true;
            cropState.dragStartX = event.clientX;
            cropState.dragStartY = event.clientY;
            cropState.dragOriginX = cropState.offsetX;
            cropState.dragOriginY = cropState.offsetY;

            if (imagePreview.setPointerCapture) {
                try {
                    imagePreview.setPointerCapture(event.pointerId);
                } catch (pointerError) {
                    // Ignore pointer capture failures and keep dragging with the window listeners.
                }
            }

            event.preventDefault();
            event.stopPropagation();
        }

        function moveDrag(event) {
            if (!cropState.isDragging || !cropState.file) {
                return;
            }

            const deltaX = event.clientX - cropState.dragStartX;
            const deltaY = event.clientY - cropState.dragStartY;
            cropState.offsetX = cropState.dragOriginX + deltaX;
            cropState.offsetY = cropState.dragOriginY + deltaY;
            updateImagePreview();
        }

        function endDrag() {
            if (!cropState.isDragging) {
                return;
            }

            cropState.isDragging = false;
            updateImagePreview();
        }

        if (triggerUpload && fileInput) {
            triggerUpload.addEventListener('click', function () {
                if (!cropState.file) {
                    fileInput.click();
                }
            });
        }

        if (imagePreview) {
            imagePreview.addEventListener('click', function (event) {
                event.stopPropagation();
            });

            imagePreview.addEventListener('pointerdown', startDrag);
        }

        window.addEventListener('pointermove', moveDrag);
        window.addEventListener('pointerup', endDrag);
        window.addEventListener('pointercancel', endDrag);
        window.addEventListener('resize', updateImagePreview);

        if (fileInput) {
            fileInput.addEventListener('change', function (e) {
                const file = e.target.files && e.target.files[0];
                if (!file || !imagePreview) {
                    cropState.file = null;
                    if (cropHint) {
                        cropHint.style.display = 'none';
                    }
                    return;
                }

                cropState.file = file;
                resetCropState();

                const reader = new FileReader();
                reader.onload = function (event) {
                    imagePreview.onload = function () {
                        cropState.naturalWidth = imagePreview.naturalWidth || 0;
                        cropState.naturalHeight = imagePreview.naturalHeight || 0;
                        updateImagePreview();
                    };

                    imagePreview.src = event.target.result;
                    imagePreview.style.display = 'block';
                    if (uploadIcon) {
                        uploadIcon.style.display = 'none';
                    }
                    if (cropHint) {
                        cropHint.style.display = 'block';
                    }
                };
                reader.readAsDataURL(file);
            });
        }

        if (form) {
            form.addEventListener('submit', async function (event) {
                if (!cropState.file || cropState.isSubmitting) {
                    return;
                }

                event.preventDefault();
                cropState.isSubmitting = true;

                try {
                    await prepareCroppedFile();
                    HTMLFormElement.prototype.submit.call(form);
                } catch (error) {
                    cropState.isSubmitting = false;
                    form.submit();
                }
            });
        }

        function getTextColorByBackground(hexColor) {
            let hex = String(hexColor || '').replace('#', '').trim();
            if (hex.length === 3) {
                hex = hex.split('').map(function (char) { return char + char; }).join('');
            }

            const r = parseInt(hex.substring(0, 2), 16);
            const g = parseInt(hex.substring(2, 4), 16);
            const b = parseInt(hex.substring(4, 6), 16);
            const luminance = (0.299 * r) + (0.587 * g) + (0.114 * b);

            return luminance > 160 ? '#1F2937' : '#F9FAFB';
        }

        function applyPreviewColor(rawColor) {
            const normalized = String(rawColor || '').replace('#', '').trim();
            const isValidHex = /^[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/.test(normalized);
            if (!isValidHex) {
                return;
            }

            let normalizedHex = normalized.toUpperCase();
            if (normalizedHex.length === 3) {
                normalizedHex = normalizedHex.split('').map(function (char) { return char + char; }).join('');
            }

            const color = '#' + normalizedHex;
            const textColor = getTextColorByBackground(color);

            previewCard.style.setProperty('background-color', color, 'important');
            previewCard.style.setProperty('color', textColor, 'important');

            if (nativeColorPicker) {
                nativeColorPicker.value = color;
            }
            
            if (postColorInput) {
                postColorInput.value = color;
            }

            const textFields = previewCard.querySelectorAll('.post-titlearea, .post-textarea');
            textFields.forEach(function (field) {
                field.style.setProperty('color', textColor, 'important');
            });
        }

        const swatches = document.querySelectorAll('.tools-area .color-swatch');
        swatches.forEach(function (swatch) {
            swatch.addEventListener('click', function () {
                applyPreviewColor(swatch.dataset.color);
            });
        });

        toolsArea.addEventListener('click', function (e) {
            const swatch = e.target.closest('.color-swatch');
            if (!swatch) {
                return;
            }
            applyPreviewColor(swatch.dataset.color);
        });

        if (nativeColorPicker) {
            nativeColorPicker.addEventListener('input', function () {
                applyPreviewColor(nativeColorPicker.value);
            });
        }

        const initialSwatch = document.querySelector('.tools-area .color-swatch');
        if (initialSwatch && initialSwatch.dataset.color) {
            applyPreviewColor(initialSwatch.dataset.color);
        }
    })();
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>

<?php $this->endBody() ?>

<?php $this->endPage() ?>