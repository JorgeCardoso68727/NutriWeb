<?php

namespace app\controllers;

use app\helpers\RolePermissionHelper;
use app\models\BadgePedido;
use app\models\Denuncia;
use app\models\Post;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class ReportsController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['create', 'dashboard', 'reports-accounts', 'reports-content', 'moderate-account', 'mark-post-report-reviewed'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'create' => ['post'],
                    'moderate-account' => ['post'],
                    'mark-post-report-reviewed' => ['post'],
                ],
            ],
        ];
    }

    public function actionCreate()
    {
        $request = Yii::$app->request;
        $targetType = strtolower(trim((string) $request->post('target_type', 'profile')));
        $targetUserId = (int) $request->post('target_user_id', 0);
        $targetPostId = (int) $request->post('target_post_id', 0);
        $motivo = trim((string) $request->post('motivo', ''));
        $descricao = trim((string) $request->post('descricao', ''));
        $currentUserId = (int) Yii::$app->user->id;

        if (!in_array($targetType, ['profile', 'post'], true)) {
            Yii::$app->session->setFlash('Profile-error', 'Tipo de reporte invalido.');
            return $this->redirect($request->referrer ?: ['/inicio']);
        }

        if ($targetUserId <= 0 || $motivo === '') {
            Yii::$app->session->setFlash('Profile-error', 'Preenche os campos obrigatorios para reportar.');
            return $this->redirect($request->referrer ?: ['/inicio']);
        }

        if ($targetUserId === $currentUserId) {
            Yii::$app->session->setFlash('Profile-error', 'Nao podes reportar a tua propria conta.');
            return $this->redirect($request->referrer ?: ['/inicio']);
        }

        $targetUserExists = (new Query())
            ->from('user')
            ->where(['id' => $targetUserId])
            ->exists();

        if (!$targetUserExists) {
            Yii::$app->session->setFlash('Profile-error', 'Conta reportada nao encontrada.');
            return $this->redirect($request->referrer ?: ['/inicio']);
        }

        if ($targetType === 'post') {
            if ($targetPostId <= 0) {
                Yii::$app->session->setFlash('Profile-error', 'Post reportado invalido.');
                return $this->redirect($request->referrer ?: ['/inicio']);
            }

            $post = Post::findOne($targetPostId);
            if ($post === null) {
                Yii::$app->session->setFlash('Profile-error', 'Post reportado nao encontrado.');
                return $this->redirect($request->referrer ?: ['/inicio']);
            }

            $targetUserId = (int) $post->user_id;
            if ($targetUserId === $currentUserId) {
                Yii::$app->session->setFlash('Profile-error', 'Nao podes reportar o teu proprio post.');
                return $this->redirect($request->referrer ?: ['/inicio']);
            }
        } else {
            $targetPostId = null;
        }

        $denuncia = new Denuncia();
        $denuncia->target_type = $targetType;
        $denuncia->target_user_id = $targetUserId;
        $denuncia->target_post_id = $targetPostId;
        $denuncia->autor_id = $currentUserId;
        $denuncia->motivo = $motivo;
        $denuncia->descricao = $descricao !== '' ? $descricao : null;
        $denuncia->data_denuncia = gmdate('Y-m-d H:i:s');
        $denuncia->estado_revisao = Denuncia::ESTADO_REVISAO_PENDENTE;

        if ($denuncia->save()) {
            Yii::$app->session->setFlash('Profile-success', 'Reporte enviado com sucesso. Obrigado pelo contributo.');
        } else {
            Yii::$app->session->setFlash('Profile-error', 'Nao foi possivel enviar o reporte.');
        }

        return $this->redirect($request->referrer ?: ['/inicio']);
    }

    public function actionDashboard()
    {
        $userReportsCount = (int) (new Query())
            ->from(Denuncia::tableName())
            ->where([
                'target_type' => 'profile',
            ])
            ->count();

        $contentReportsCount = (int) (new Query())
            ->from(Denuncia::tableName())
            ->where(['target_type' => 'post'])
            ->count();

        $badgesCount = (int) (new Query())
            ->from(BadgePedido::tableName())
            ->where(['estado' => BadgePedido::ESTADO_PENDENTE])
            ->count();

        return $this->render('@app/views/user/default/dashboard', [
            'userReportsCount' => $userReportsCount,
            'contentReportsCount' => $contentReportsCount,
            'badgesCount' => $badgesCount,
        ]);
    }

    public function actionReportsAccounts()
    {
        if (!RolePermissionHelper::isCurrentUserAdmin()) {
            throw new NotFoundHttpException('Pagina nao encontrada.');
        }

        $accountReports = (new Query())
            ->select([
                'b.id',
                'b.target_user_id',
                'b.target_post_id',
                'b.target_type',
                'b.motivo',
                'b.descricao',
                'b.data_denuncia AS created_at',
                'u.username as reportado_username',
                'rep.username as reporter_username',
            ])
            ->from(['b' => Denuncia::tableName()])
            ->innerJoin(['u' => 'user'], 'u.id = b.target_user_id')
            ->leftJoin(['rep' => 'user'], 'rep.id = b.autor_id')
            ->where(['b.target_type' => 'profile'])
            ->orderBy(['b.id' => SORT_DESC])
            ->all();

        return $this->render('@app/views/user/default/reports-accounts', [
            'accountReports' => $accountReports,
            'accountReportsCount' => count($accountReports),
        ]);
    }

    public function actionReportsContent()
    {
        if (!RolePermissionHelper::isCurrentUserAdmin()) {
            throw new NotFoundHttpException('Pagina nao encontrada.');
        }

        $contentReports = (new Query())
            ->select([
                'r.id AS report_id',
                'p.id AS post_id',
                'p.titulo',
                'r.target_post_id',
                'r.motivo',
                'r.descricao',
                'r.estado_revisao',
                'r.data_denuncia AS created_at',
                'u.username as reportado_username',
                'rep.username as reporter_username',
            ])
            ->from(['r' => Denuncia::tableName()])
            ->leftJoin(['p' => 'post'], 'p.id = r.target_post_id')
            ->leftJoin(['u' => 'user'], 'u.id = p.user_id')
            ->leftJoin(['rep' => 'user'], 'rep.id = r.autor_id')
            ->where([
                'r.target_type' => 'post',
                'r.estado_revisao' => Denuncia::ESTADO_REVISAO_PENDENTE,
            ])
            ->orderBy(['r.id' => SORT_DESC])
            ->all();

        return $this->render('@app/views/user/default/reports-content', [
            'contentReports' => $contentReports,
            'contentReportsCount' => (int) (new Query())
                ->from(Denuncia::tableName())
                ->where([
                    'target_type' => 'post',
                    'estado_revisao' => Denuncia::ESTADO_REVISAO_PENDENTE,
                ])
                ->count(),
        ]);
    }

    public function actionMarkPostReportReviewed($id)
    {
        if (!RolePermissionHelper::isCurrentUserAdmin()) {
            throw new NotFoundHttpException('Pagina nao encontrada.');
        }

        $reportId = (int) $id;
        if ($reportId <= 0) {
            throw new NotFoundHttpException('Report invalido.');
        }

        $report = Denuncia::findOne($reportId);
        if ($report === null || $report->target_type !== 'post') {
            throw new NotFoundHttpException('Report de post nao encontrado.');
        }

        if ($report->estado_revisao !== Denuncia::ESTADO_REVISAO_REVISTO) {
            $report->estado_revisao = Denuncia::ESTADO_REVISAO_REVISTO;
            if ($report->save(false)) {
                return $this->redirect(['/reports/reports-content']);
            } else {
                Yii::$app->session->setFlash('Profile-error', 'Nao foi possivel atualizar o estado do report.');
            }
        }

        return $this->redirect(['/reports/reports-content']);
    }

    public function actionModerateAccount($id, $acao)
    {
        if (!RolePermissionHelper::isCurrentUserAdmin()) {
            throw new NotFoundHttpException('Pagina nao encontrada.');
        }

        $targetUserId = (int) $id;
        if ($targetUserId <= 0) {
            throw new NotFoundHttpException('Conta invalida.');
        }

        $username = (new Query())
            ->select(['username'])
            ->from('user')
            ->where(['id' => $targetUserId])
            ->scalar();

        if (!is_string($username) || $username === '') {
            throw new NotFoundHttpException('Conta nao encontrada.');
        }

        if ($acao === 'nao-banir') {
            Yii::$app->session->setFlash('Profile-success', 'Conta revista e mantida ativa.');
            return $this->redirect('/' . $username . '?rever=1');
        }

        if ($acao !== 'banir') {
            Yii::$app->session->setFlash('Profile-error', 'Acao invalida.');
            return $this->redirect('/' . $username . '?rever=1');
        }

        $userTableSchema = Yii::$app->db->schema->getTableSchema('user', true);
        if ($userTableSchema === null) {
            Yii::$app->session->setFlash('Profile-error', 'Tabela de utilizadores indisponivel.');
            return $this->redirect('/' . $username . '?rever=1');
        }

        $updates = [];
        if (isset($userTableSchema->columns['status'])) {
            $updates['status'] = 0;
        }
        if (isset($userTableSchema->columns['banned_at'])) {
            $updates['banned_at'] = gmdate('Y-m-d H:i:s');
        }
        if (isset($userTableSchema->columns['banned_reason'])) {
            $updates['banned_reason'] = 'Banido via revisao de reports';
        }

        if (empty($updates)) {
            Yii::$app->session->setFlash('Profile-error', 'Nao foi possivel aplicar banimento nesta base de dados.');
            return $this->redirect('/' . $username . '?rever=1');
        }

        Yii::$app->db->createCommand()
            ->update('user', $updates, ['id' => $targetUserId])
            ->execute();

        Yii::$app->session->setFlash('Profile-success', 'Conta banida com sucesso.');
        return $this->redirect('/reports-contas');
    }
}
