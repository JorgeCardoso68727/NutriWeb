<?php

use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var string $subject
 * @var \amnah\yii2\user\models\User $user
 * @var \amnah\yii2\user\models\UserToken $userToken
 */

$url = Url::toRoute(["/user/default/forgot", "token" => $userToken->token], true);
?>

<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin: 0; padding: 24px 12px; background-color: #f6f6ea; font-family: Arial, sans-serif; color: #2d2d2d;">
    <tr>
        <td align="center">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width: 620px; background-color: #ffffff; border: 1px solid #dfe7d5; border-radius: 18px; overflow: hidden;">
                <tr>
                    <td style="padding: 28px 32px 18px 32px; background: linear-gradient(135deg, #e6f6dc 0%, #f6f6ea 100%); border-bottom: 1px solid #dfe7d5;">
                        <div style="font-size: 12px; letter-spacing: 0.12em; color: #2f6b2f; font-weight: 700; text-transform: uppercase;">NutriWeb</div>
                        <h1 style="margin: 10px 0 0 0; font-size: 24px; line-height: 1.2; color: #195321;">Reposicao de password</h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 28px 32px 10px 32px; font-size: 16px; line-height: 1.6;">
                        <p style="margin: 0 0 14px 0;">Ola <strong><?= Html::encode($user->username) ?></strong>,</p>
                        <p style="margin: 0 0 14px 0;">Recebemos um pedido para redefinir a password da tua conta.</p>
                        <p style="margin: 0 0 20px 0;">Clica no botao abaixo para continuar:</p>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding: 0 32px 20px 32px;">
                        <?= Html::a(
                            'Repor Password',
                            $url,
                            [
                                'style' => 'display:inline-block; background-color:#238230; color:#ffffff; text-decoration:none; font-weight:700; font-size:16px; padding:12px 26px; border-radius:14px;'
                            ]
                        ) ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0 32px 26px 32px; font-size: 14px; line-height: 1.6; color: #4f5f4a;">
                        <p style="margin: 0 0 10px 0;"><strong>Este link expira em 24 horas.</strong></p>
                        <p style="margin: 0 0 8px 0;">Se o botao nao funcionar, copia e cola este link no navegador:</p>
                        <p style="margin: 0; padding: 12px; background-color: #f7fbf2; border: 1px solid #d6e6c8; border-radius: 10px; word-break: break-all; color: #32523b;"><?= Html::encode($url) ?></p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 16px 32px 24px 32px; border-top: 1px solid #e8eee0; font-size: 12px; line-height: 1.5; color: #6c7a69; background-color: #fcfdf9;">
                        Se nao pediste para repor a password, podes ignorar este email com seguranca.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
