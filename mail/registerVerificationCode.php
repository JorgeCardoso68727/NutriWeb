<?php

use yii\helpers\Html;

/**
 * @var string $subject
 * @var string $username
 * @var string $code
 */
?>

<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin: 0; padding: 24px 12px; background-color: #f6f6ea; font-family: Arial, sans-serif; color: #2d2d2d;">
    <tr>
        <td align="center">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width: 620px; background-color: #ffffff; border: 1px solid #dfe7d5; border-radius: 18px; overflow: hidden;">
                <tr>
                    <td style="padding: 28px 32px 18px 32px; background: linear-gradient(135deg, #e6f6dc 0%, #f6f6ea 100%); border-bottom: 1px solid #dfe7d5;">
                        <div style="font-size: 12px; letter-spacing: 0.12em; color: #2f6b2f; font-weight: 700; text-transform: uppercase;">NutriWeb</div>
                        <h1 style="margin: 10px 0 0 0; font-size: 24px; line-height: 1.2; color: #195321;">Confirmacao de e-mail</h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 28px 32px 12px 32px; font-size: 16px; line-height: 1.6;">
                        <p style="margin: 0 0 14px 0;">Ola <strong><?= Html::encode($username) ?></strong>,</p>
                        <p style="margin: 0 0 14px 0;">Usa o codigo abaixo para concluir o teu registo:</p>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding: 0 32px 20px 32px;">
                        <div style="display: inline-block; padding: 10px 24px; border-radius: 12px; background-color: #f2f9ec; border: 1px solid #d6e6c8; font-size: 30px; letter-spacing: 0.2em; font-weight: 700; color: #1d5f2a;">
                            <?= Html::encode($code) ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0 32px 26px 32px; font-size: 14px; line-height: 1.6; color: #4f5f4a;">
                        <p style="margin: 0 0 10px 0;"><strong>Este codigo expira em 10 minutos.</strong></p>
                        <p style="margin: 0;">Se nao criaste esta conta, ignora este email com seguranca.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
