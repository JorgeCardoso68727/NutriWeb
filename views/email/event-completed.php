<?php

/**
 * @var yii\web\View $this
 * @var amnah\yii2\user\models\User $user
 * @var app\models\Event $event
 * @var string $eventUrl
 */

use yii\helpers\Html;
?>

<div style="font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto;">
    <div style="background-color: #1e6b1e; color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center;">
        <h1 style="margin: 0; font-size: 24px;">Evento Encerrado</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px;">
        <p>Olá <?= Html::encode($user->username) ?>,</p>

        <p>O seguinte evento em que participaste foi encerrado:</p>

        <div style="background-color: #f0f8f0; border-left: 4px solid #1e6b1e; padding: 20px; margin: 20px 0; border-radius: 4px;">
            <h2 style="margin: 0 0 10px 0; color: #1e6b1e;">
                <?= Html::encode($event->title) ?>
            </h2>

            <p style="margin: 8px 0; color: #666;">
                <strong>Categoria:</strong> <?= Html::encode($event->category->name ?? 'Sem categoria') ?>
            </p>

            <p style="margin: 8px 0; color: #666;">
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
            Agradecemos a tua participacao! Se gostaste do evento, considera convidar amigos para futuros eventos.
        </p>

        <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #999; font-size: 12px;">
            Este é um email automático de notificação. Por favor, não respondas a este email.
        </p>
    </div>
</div>