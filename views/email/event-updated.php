<?php

/**
 * @var yii\web\View $this
 * @var amnah\yii2\user\models\User $user
 * @var app\models\Event $event
 * @var string $eventUrl
 * @var array $changedFields
 */

use yii\helpers\Html;
?>

<div style="font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto;">
    <div style="background-color: #e89c1f; color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center;">
        <h1 style="margin: 0; font-size: 24px;">Evento Atualizado</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px;">
        <p>Olá <?= Html::encode($user->username) ?>,</p>

        <p>O seguinte evento em que te inscreveste foi atualizado:</p>

        <div style="background-color: #fef9f0; border-left: 4px solid #e89c1f; padding: 20px; margin: 20px 0; border-radius: 4px;">
            <h2 style="margin: 0 0 10px 0; color: #e89c1f;">
                <?= Html::encode($event->title) ?>
            </h2>

            <?php if (!empty($changedFields)): ?>
                <p style="margin: 12px 0; color: #d16600; font-weight: bold;">Mudancas realizadas:</p>
                <ul style="margin: 8px 0; padding-left: 20px; color: #666;">
                    <?php foreach ($changedFields as $field => $change): ?>
                        <li>
                            <strong><?= Html::encode($field) ?></strong>:
                            <?php if (is_array($change)): ?>
                                de "<?= Html::encode((string) ($change['old'] ?? '')) ?>" para "<?= Html::encode((string) ($change['new'] ?? '')) ?>"
                            <?php else: ?>
                                <?= Html::encode((string) $change) ?>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <p style="margin: 12px 0 8px 0; color: #666;">
                <strong>A partir de</strong> <?= Html::encode($event->start_date) ?>
                <?php if (!empty($event->end_date)): ?>
                    até <?= Html::encode($event->end_date) ?>
                <?php endif; ?>
            </p>

            <p style="margin: 8px 0; color: #666;">
                <strong>Localização:</strong> <?= Html::encode($event->location ?: 'A definir') ?>
            </p>
        </div>

        <p>
            <a href="<?= Html::encode($eventUrl) ?>" style="display: inline-block; background-color: #238230; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;">
                Ver Evento Atualizado
            </a>
        </p>

        <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #999; font-size: 12px;">
            Recebeste este email porque te inscreveste neste evento. Este é um email automático, por favor não respondas.
        </p>
    </div>
</div>