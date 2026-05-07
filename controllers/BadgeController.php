<?php

namespace app\controllers;

use app\helpers\RolePermissionHelper;
use app\models\BadgePedido;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
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
        $requestType = (string) Yii::$app->request->get('tipo', 'nutricionista');
        if ($requestType !== 'instituicao') {
            $requestType = 'nutricionista';
        }

        $userId = (int) Yii::$app->user->id;
        $isAdmin = RolePermissionHelper::isCurrentUserAdmin();
        $profile = Yii::$app->user->identity->profile;
        $fullName = trim(($profile->Frist_Name ?? '') . ' ' . ($profile->Last_Name ?? ''));

        if (!$isAdmin && Yii::$app->request->isPost) {
            $uploadedPdf = UploadedFile::getInstanceByName($requestType === 'instituicao' ? 'instituicaoPdf' : 'diplomaPdf');

            if ($uploadedPdf === null) {
                Yii::$app->session->setFlash('Badge-error', 'Seleciona um ficheiro PDF.');
                return $this->refresh();
            }

            $extension = strtolower((string) $uploadedPdf->extension);
            if ($extension !== 'pdf') {
                Yii::$app->session->setFlash('Badge-error', 'O ficheiro tem de ser PDF.');
                return $this->refresh();
            }

            // Validar campos da instituição ou nutricionista
            if ($requestType === 'instituicao') {
                $nomeCompleto = trim((string) Yii::$app->request->post('nomeCompleto', ''));
                $nomeInstituicao = trim((string) Yii::$app->request->post('nomeInstituicao', ''));
                $numeroFiscal = trim((string) Yii::$app->request->post('numeroFiscal', ''));

                if (empty($nomeCompleto)) {
                    Yii::$app->session->setFlash('Badge-error', 'O nome completo é obrigatório.');
                    return $this->refresh();
                }
                if (empty($nomeInstituicao)) {
                    Yii::$app->session->setFlash('Badge-error', 'O nome da instituição é obrigatório.');
                    return $this->refresh();
                }
                if (empty($numeroFiscal)) {
                    Yii::$app->session->setFlash('Badge-error', 'O número fiscal é obrigatório.');
                    return $this->refresh();
                }

                if (!self::isValidPortugueseNif($numeroFiscal)) {
                    Yii::$app->session->setFlash('Badge-error', 'O número fiscal (NIF) é inválido.');
                    return $this->refresh();
                }

                $numeroFiscal = preg_replace('/\D+/', '', $numeroFiscal);
            } else {
                // Validar nome completo para nutricionista
                $nomeCompletoNutricionista = trim((string) Yii::$app->request->post('nomeCompletoNutricionista', ''));

                if (empty($nomeCompletoNutricionista)) {
                    Yii::$app->session->setFlash('Badge-error', 'O nome completo é obrigatório.');
                    return $this->refresh();
                }
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

            // Preparar observacao com informações de tipo e dados
            if ($requestType === 'instituicao') {
                $nomeCompleto = trim((string) Yii::$app->request->post('nomeCompleto', ''));
                $nomeInstituicao = trim((string) Yii::$app->request->post('nomeInstituicao', ''));
                $numeroFiscal = trim((string) Yii::$app->request->post('numeroFiscal', ''));

                $obsData = [
                    'tipo' => 'instituicao',
                    'nomeCompleto' => $nomeCompleto,
                    'nomeInstituicao' => $nomeInstituicao,
                    'numeroFiscal' => $numeroFiscal,
                ];
                $pedido->observacao = json_encode($obsData);
            } else {
                // Para nutricionista, guardar o nome que foi escrito no formulário
                $nomeCompletoNutricionista = trim((string) Yii::$app->request->post('nomeCompletoNutricionista', ''));

                $obsData = [
                    'tipo' => 'nutricionista',
                    'nomeCompleto' => $nomeCompletoNutricionista,
                ];
                $pedido->observacao = json_encode($obsData);
            }

            if ($pedido->save()) {
                Yii::$app->session->setFlash('Badge-success', $requestType === 'instituicao'
                    ? 'Pedido de instituicao enviado para analise do administrador.'
                    : 'Pedido de badge enviado para analise do administrador.');
            } else {
                Yii::$app->session->setFlash('Badge-error', 'Nao foi possivel guardar o pedido.');
            }

            return $this->refresh();
        }

        $lastPedidoQuery = (new Query())
            ->from(BadgePedido::tableName())
            ->where(['user_id' => $userId]);

        if ($requestType === 'instituicao') {
            $lastPedidoQuery->andWhere(["like", 'observacao', 'instituicao']);
        } else {
            $lastPedidoQuery->andWhere([
                'or',
                ['observacao' => null],
                ['not like', 'observacao', 'instituicao'],
            ]);
        }

        $lastPedido = $lastPedidoQuery
            ->orderBy(['id' => SORT_DESC])
            ->one();

        $lastPedidoEstado = trim((string) ($lastPedido['estado'] ?? ''));
        $lastPedidoPdf = trim((string) ($lastPedido['diploma_pdf'] ?? ''));
        $hasLastPedido = $lastPedidoEstado !== '' || $lastPedidoPdf !== '';

        $pendingRequests = [];
        if ($isAdmin) {
            $pendingQuery = (new Query())
                ->select([
                    'b.id',
                    'b.user_id',
                    'b.diploma_pdf',
                    'b.estado',
                    'b.observacao',
                    'b.created_at',
                    'u.username',
                ])
                ->from(['b' => BadgePedido::tableName()])
                ->innerJoin(['u' => 'user'], 'u.id = b.user_id')
                ->where(['b.estado' => BadgePedido::ESTADO_PENDENTE]);

            if ($requestType === 'instituicao') {
                $pendingQuery->andWhere(["like", 'b.observacao', 'instituicao']);
            } else {
                $pendingQuery->andWhere([
                    'or',
                    ['b.observacao' => null],
                    ['not like', 'b.observacao', 'instituicao'],
                ]);
            }

            $pendingRequests = $pendingQuery
                ->orderBy(['b.id' => SORT_DESC])
                ->all();
        }

        if (Yii::$app->request->isAjax && (int) Yii::$app->request->get('refresh', 0) === 1) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($isAdmin) {
                $currentRequestType = $requestType;
                $rows = array_map(static function (array $row) use ($currentRequestType): array {
                    $data = [
                        'id' => (int) ($row['id'] ?? 0),
                        'username' => trim((string) ($row['username'] ?? '')),
                        'pdf_url' => Url::to('@web/' . ltrim((string) ($row['diploma_pdf'] ?? ''), '/')),
                        'created_at' => trim((string) ($row['created_at'] ?? '')),
                        'approve_url' => Url::to(['/badge/badge-review', 'id' => (int) ($row['id'] ?? 0), 'acao' => 'aprovar', 'tipo' => $currentRequestType]),
                        'reject_url' => Url::to(['/badge/badge-review', 'id' => (int) ($row['id'] ?? 0), 'acao' => 'rejeitar', 'tipo' => $currentRequestType]),
                    ];

                    // Adicionar dados da instituição ou nutricionista se disponível
                    if (!empty($row['observacao'])) {
                        $obsData = json_decode($row['observacao'], true);
                        if (is_array($obsData)) {
                            $tipo = $obsData['tipo'] ?? '';
                            if ($tipo === 'instituicao') {
                                $data['nomeCompleto'] = $obsData['nomeCompleto'] ?? '';
                                $data['nomeInstituicao'] = $obsData['nomeInstituicao'] ?? '';
                                $data['numeroFiscal'] = $obsData['numeroFiscal'] ?? '';
                            } else {
                                // Nutricionista
                                $data['nomeCompleto'] = $obsData['nomeCompleto'] ?? '';
                            }
                        }
                    }

                    return $data;
                }, $pendingRequests);

                return [
                    'success' => true,
                    'is_admin' => true,
                    'count' => count($rows),
                    'rows' => $rows,
                ];
            }

            return [
                'success' => true,
                'is_admin' => false,
                'has_last_pedido' => $hasLastPedido,
                'last_pedido_estado' => $lastPedidoEstado,
                'last_pedido_pdf_url' => $lastPedidoPdf !== '' ? Url::to('@web/' . ltrim($lastPedidoPdf, '/')) : '',
            ];
        }

        return $this->render('@app/views/user/default/badge', [
            'isAdmin' => $isAdmin,
            'pendingRequests' => $pendingRequests,
            'fullName' => $fullName,
            'hasLastPedido' => $hasLastPedido,
            'lastPedidoEstado' => $lastPedidoEstado,
            'lastPedidoPdf' => $lastPedidoPdf,
            'requestType' => $requestType,
        ]);
    }

    public function actionBadgeReview($id, $acao)
    {
        if (!RolePermissionHelper::isCurrentUserAdmin()) {
            throw new NotFoundHttpException('Pagina nao encontrada.');
        }

        $requestType = (string) Yii::$app->request->get('tipo', 'nutricionista');
        if ($requestType !== 'instituicao') {
            $requestType = 'nutricionista';
        }

        $pedido = (new Query())
            ->from(BadgePedido::tableName())
            ->where(['id' => (int) $id])
            ->one();
        if ($pedido === null) {
            throw new NotFoundHttpException('Pedido nao encontrado.');
        }

        // Verificar se é um pedido de instituição
        $isPedidoInstituicao = false;
        if (!empty($pedido['observacao'])) {
            $obsData = json_decode($pedido['observacao'], true);
            if (is_array($obsData) && ($obsData['tipo'] ?? null) === 'instituicao') {
                $isPedidoInstituicao = true;
            } elseif (str_starts_with((string) $pedido['observacao'], 'tipo:instituicao')) {
                $isPedidoInstituicao = true;
            } elseif (stripos((string) $pedido['observacao'], 'instituicao') !== false) {
                $isPedidoInstituicao = true;
            }
        }

        if (($requestType === 'instituicao') !== $isPedidoInstituicao) {
            Yii::$app->session->setFlash('Badge-error', 'Pedido invalido para este tipo de aprovacao.');
            return $this->redirect(['/badge', 'tipo' => $requestType]);
        }

        if ($pedido['estado'] !== BadgePedido::ESTADO_PENDENTE) {
            Yii::$app->session->setFlash('Badge-error', 'Este pedido ja foi processado.');
            return $this->redirect(['/badge', 'tipo' => $requestType]);
        }

        if ($acao === 'aprovar') {
            $novoEstado = BadgePedido::ESTADO_APROVADO;
        } elseif ($acao === 'rejeitar') {
            $novoEstado = BadgePedido::ESTADO_REJEITADO;
        } else {
            Yii::$app->session->setFlash('Badge-error', 'Acao invalida.');
            return $this->redirect(['/badge', 'tipo' => $requestType]);
        }

        $pedidoModel = BadgePedido::findOne((int) $id);
        if ($pedidoModel === null) {
            throw new NotFoundHttpException('Pedido nao encontrado.');
        }

        $pedidoModel->estado = $novoEstado;
        $pedidoModel->admin_user_id = (int) Yii::$app->user->id;

        if (!$pedidoModel->save(false)) {
            Yii::$app->session->setFlash('Badge-error', 'Nao foi possivel atualizar o pedido.');
            return $this->redirect(['/badge', 'tipo' => $requestType]);
        }

        if ($novoEstado === BadgePedido::ESTADO_APROVADO) {
            $permissionColumn = $isPedidoInstituicao ? 'can_instituicao' : 'can_nutricionista';

            $roleSchema = Yii::$app->db->schema->getTableSchema('role', true);
            $userSchema = Yii::$app->db->schema->getTableSchema('user', true);
            $canAssignRole = $roleSchema !== null
                && $userSchema !== null
                && isset($roleSchema->columns[$permissionColumn])
                && isset($userSchema->columns['role_id']);

            if ($canAssignRole) {
                $roleId = (new Query())
                    ->select(['id'])
                    ->from('role')
                    ->where([$permissionColumn => 1])
                    ->orderBy(['id' => SORT_ASC])
                    ->scalar();

                if ($roleId !== false && $roleId !== null) {
                    Yii::$app->db->createCommand()
                        ->update('user', ['role_id' => (int) $roleId], ['id' => (int) $pedidoModel->user_id])
                        ->execute();
                }
            }
        }

        Yii::$app->session->setFlash('Badge-success', 'Pedido atualizado com sucesso.');
        return $this->redirect(['/badge', 'tipo' => $requestType]);
    }

    private static function isValidPortugueseNif(string $value): bool
    {
        $digits = preg_replace('/\D+/', '', $value);
        if ($digits === null || strlen($digits) !== 9) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum += ((int) $digits[$i]) * (9 - $i);
        }

        $remainder = $sum % 11;
        $checkDigit = $remainder < 2 ? 0 : 11 - $remainder;

        return $checkDigit === (int) $digits[8];
    }
}
