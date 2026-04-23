<?php

namespace app\controllers;

use app\helpers\RolePermissionHelper;
use app\models\BadgePedido;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class BadgeController extends Controller
{
    /**
     * @var \amnah\yii2\user\Module
     */
    public $module;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['badge', 'badge-review'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'badge-review' => ['post'],
                ],
            ],
        ];
    }

    public function actionBadge()
    {
        $userId = (int) Yii::$app->user->id;
        $isAdmin = RolePermissionHelper::isCurrentUserAdmin();
        $profile = Yii::$app->user->identity->profile;
        $fullName = trim(($profile->Frist_Name ?? '') . ' ' . ($profile->Last_Name ?? ''));

        if (!$isAdmin && Yii::$app->request->isPost) {
            $uploadedPdf = UploadedFile::getInstanceByName('diplomaPdf');

            if ($uploadedPdf === null) {
                Yii::$app->session->setFlash('Badge-error', 'Seleciona um ficheiro PDF.');
                return $this->refresh();
            }

            $extension = strtolower((string) $uploadedPdf->extension);
            if ($extension !== 'pdf') {
                Yii::$app->session->setFlash('Badge-error', 'O ficheiro tem de ser PDF.');
                return $this->refresh();
            }

            $uploadDir = Yii::getAlias('@webroot/uploads/certificados');
            FileHelper::createDirectory($uploadDir);

            $safeBaseName = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($uploadedPdf->name, PATHINFO_FILENAME));
            $safeBaseName = $safeBaseName ?: 'certificado';
            $fileName = $userId . '_' . time() . '_' . $safeBaseName . '.pdf';
            $fullPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

            if (!$uploadedPdf->saveAs($fullPath)) {
                Yii::$app->session->setFlash('Badge-error', 'Nao foi possivel guardar o PDF.');
                return $this->refresh();
            }

            $pedido = new BadgePedido();
            $pedido->user_id = $userId;
            $pedido->diploma_pdf = 'uploads/certificados/' . $fileName;
            $pedido->estado = BadgePedido::ESTADO_PENDENTE;

            if ($pedido->save()) {
                Yii::$app->session->setFlash('Badge-success', 'Pedido de badge enviado para analise do administrador.');
            } else {
                Yii::$app->session->setFlash('Badge-error', 'Nao foi possivel guardar o pedido.');
            }

            return $this->refresh();
        }

        $lastPedido = (new Query())
            ->from(BadgePedido::tableName())
            ->where(['user_id' => $userId])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        $lastPedidoEstado = trim((string) ($lastPedido['estado'] ?? ''));
        $lastPedidoPdf = trim((string) ($lastPedido['diploma_pdf'] ?? ''));
        $hasLastPedido = $lastPedidoEstado !== '' || $lastPedidoPdf !== '';

        $pendingRequests = [];
        if ($isAdmin) {
            $pendingRequests = (new Query())
                ->select([
                    'b.id',
                    'b.user_id',
                    'b.diploma_pdf',
                    'b.estado',
                    'b.created_at',
                    'u.username',
                ])
                ->from(['b' => BadgePedido::tableName()])
                ->innerJoin(['u' => 'user'], 'u.id = b.user_id')
                ->where(['b.estado' => BadgePedido::ESTADO_PENDENTE])
                ->orderBy(['b.id' => SORT_DESC])
                ->all();
        }

        return $this->render('@app/views/user/default/badge', [
            'isAdmin' => $isAdmin,
            'pendingRequests' => $pendingRequests,
            'fullName' => $fullName,
            'hasLastPedido' => $hasLastPedido,
            'lastPedidoEstado' => $lastPedidoEstado,
            'lastPedidoPdf' => $lastPedidoPdf,
        ]);
    }

    public function actionBadgeReview($id, $acao)
    {
        if (!RolePermissionHelper::isCurrentUserAdmin()) {
            throw new NotFoundHttpException('Pagina nao encontrada.');
        }

        $pedido = (new Query())
            ->from(BadgePedido::tableName())
            ->where(['id' => (int) $id])
            ->one();
        if ($pedido === null) {
            throw new NotFoundHttpException('Pedido nao encontrado.');
        }

        if ($pedido['estado'] !== BadgePedido::ESTADO_PENDENTE) {
            Yii::$app->session->setFlash('Badge-error', 'Este pedido ja foi processado.');
            return $this->redirect(['/badge']);
        }

        if ($acao === 'aprovar') {
            $novoEstado = BadgePedido::ESTADO_APROVADO;
        } elseif ($acao === 'rejeitar') {
            $novoEstado = BadgePedido::ESTADO_REJEITADO;
        } else {
            Yii::$app->session->setFlash('Badge-error', 'Acao invalida.');
            return $this->redirect(['/badge']);
        }

        $pedidoModel = BadgePedido::findOne((int) $id);
        if ($pedidoModel === null) {
            throw new NotFoundHttpException('Pedido nao encontrado.');
        }

        $pedidoModel->estado = $novoEstado;
        $pedidoModel->admin_user_id = (int) Yii::$app->user->id;

        if (!$pedidoModel->save(false)) {
            Yii::$app->session->setFlash('Badge-error', 'Nao foi possivel atualizar o pedido.');
            return $this->redirect(['/badge']);
        }

        if ($novoEstado === BadgePedido::ESTADO_APROVADO) {
            $roleSchema = Yii::$app->db->schema->getTableSchema('role', true);
            $userSchema = Yii::$app->db->schema->getTableSchema('user', true);
            $canAssignNutritionistRole = $roleSchema !== null
                && $userSchema !== null
                && isset($roleSchema->columns['can_nutricionista'])
                && isset($userSchema->columns['role_id']);

            if ($canAssignNutritionistRole) {
                $nutritionistRoleId = (new Query())
                    ->select(['id'])
                    ->from('role')
                    ->where(['can_nutricionista' => 1])
                    ->orderBy(['id' => SORT_ASC])
                    ->scalar();

                if ($nutritionistRoleId !== false && $nutritionistRoleId !== null) {
                    Yii::$app->db->createCommand()
                        ->update('user', ['role_id' => (int) $nutritionistRoleId], ['id' => (int) $pedidoModel->user_id])
                        ->execute();
                }
            }
        }

        Yii::$app->session->setFlash('Badge-success', 'Pedido atualizado com sucesso.');
        return $this->redirect(['/badge']);
    }
}
