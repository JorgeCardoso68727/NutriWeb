<?php

namespace app\controllers;

use app\helpers\RolePermissionHelper;
use app\models\BadgePedido;
use app\models\Denuncia;
use app\models\Event;
use app\models\Post;
use amnah\yii2\user\models\Role;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\ActiveForm;

class ReportsController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['create', 'dashboard', 'reports-accounts', 'reports-content', 'reports-events', 'moderate-account', 'mark-post-report-reviewed', 'mark-account-report-reviewed', 'mark-event-report-reviewed', 'admin-create'],
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
                    'mark-account-report-reviewed' => ['post'],
                    'mark-event-report-reviewed' => ['post'],
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
        $targetEventId = (int) $request->post('target_event_id', 0);
        $motivo = trim((string) $request->post('motivo', ''));
        $descricao = trim((string) $request->post('descricao', ''));
        $currentUserId = (int) Yii::$app->user->id;

        if (!in_array($targetType, ['profile', 'post', 'event'], true)) {
            Yii::$app->session->setFlash('Profile-error', 'Tipo de reporte invalido.');
            return $this->redirect($request->referrer ?: ['/inicio']);
        }

        if ($motivo === '') {
            Yii::$app->session->setFlash('Profile-error', 'Preenche os campos obrigatorios para reportar.');
            return $this->redirect($request->referrer ?: ['/inicio']);
        }

        if ($targetType === 'profile') {
            if ($targetUserId <= 0) {
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

        if ($targetType === 'event') {
            if ($targetEventId <= 0) {
                Yii::$app->session->setFlash('Profile-error', 'Evento reportado invalido.');
                return $this->redirect($request->referrer ?: ['/inicio']);
            }

            $event = Event::findOne($targetEventId);
            if ($event === null) {
                Yii::$app->session->setFlash('Profile-error', 'Evento reportado nao encontrado.');
                return $this->redirect($request->referrer ?: ['/inicio']);
            }

            $targetUserId = (int) $event->creator_id;
            if ($targetUserId === $currentUserId) {
                Yii::$app->session->setFlash('Profile-error', 'Nao podes reportar o teu proprio evento.');
                return $this->redirect($request->referrer ?: ['/inicio']);
            }

            $targetPostId = null;
        } else {
            $targetEventId = null;
        }

        $denuncia = new Denuncia();
        $denuncia->target_type = $targetType;
        $denuncia->target_user_id = $targetUserId;
        $denuncia->target_post_id = $targetPostId;
        $denuncia->target_event_id = $targetEventId;
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
                'estado_revisao' => Denuncia::ESTADO_REVISAO_PENDENTE,
            ])
            ->count();

        $contentReportsCount = (int) (new Query())
            ->from(Denuncia::tableName())
            ->where(['target_type' => 'post'] + ['estado_revisao' => Denuncia::ESTADO_REVISAO_PENDENTE])
            ->count();

        $eventReportsCount = (int) (new Query())
            ->from(Denuncia::tableName())
            ->where(['target_type' => 'event'] + ['estado_revisao' => Denuncia::ESTADO_REVISAO_PENDENTE])
            ->count();

        $badgesNutricionistaCount = (int) (new Query())
            ->from(BadgePedido::tableName())
            ->where(['estado' => BadgePedido::ESTADO_PENDENTE])
            ->andWhere([
                'or',
                ['observacao' => null],
                ['not like', 'observacao', 'instituicao'],
            ])
            ->count();

        $badgesInstituicaoCount = (int) (new Query())
            ->from(BadgePedido::tableName())
            ->where(['estado' => BadgePedido::ESTADO_PENDENTE])
            ->andWhere(["like", 'observacao', 'instituicao'])
            ->count();

        return $this->render('@app/views/user/default/dashboard', [
            'userReportsCount' => $userReportsCount,
            'contentReportsCount' => $contentReportsCount,
            'eventReportsCount' => $eventReportsCount,
            'badgesNutricionistaCount' => $badgesNutricionistaCount,
            'badgesInstituicaoCount' => $badgesInstituicaoCount,
        ]);
    }

    public function actionAdminCreate()
    {
        if (!RolePermissionHelper::isCurrentUserAdmin()) {
            throw new NotFoundHttpException('Pagina nao encontrada.');
        }

        $userModule = Yii::$app->getModule('user');
        $user = $userModule->model('User', ['scenario' => 'register']);
        $profile = $userModule->model('Profile');

        if ($user->role_id === null) {
            $user->role_id = Role::ROLE_ADMIN;
        }

        $post = Yii::$app->request->post();
        $userLoaded = $user->load($post);
        $profileLoaded = $profile->load($post);

        if ($userLoaded || $profileLoaded) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($user, $profile);
            }

            if ($user->validate() && $profile->validate()) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $user->setRegisterAttributes(Role::ROLE_ADMIN, $user::STATUS_ACTIVE);
                    if (!$user->save()) {
                        throw new \RuntimeException('Nao foi possivel criar a conta admin: ' . json_encode($user->getErrors(), JSON_UNESCAPED_UNICODE));
                    }

                    $profile->setUser($user->id);
                    if (!$profile->save()) {
                        throw new \RuntimeException('Nao foi possivel criar o perfil admin: ' . json_encode($profile->getErrors(), JSON_UNESCAPED_UNICODE));
                    }

                    $transaction->commit();
                    Yii::$app->session->setFlash('AdminCreate-success', 'Conta admin criada com sucesso.');
                    return $this->redirect(['/reports/dashboard']);
                } catch (\Throwable $throwable) {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('AdminCreate-error', $throwable->getMessage());
                }
            }
        }

        return $this->render('@app/views/user/default/admin-create', [
            'user' => $user,
            'profile' => $profile,
        ]);
    }

    public function actionReportsAccounts()
    {
        if (!RolePermissionHelper::isCurrentUserAdmin()) {
            throw new NotFoundHttpException('Pagina nao encontrada.');
        }

        [$accountReportsDailyLabels, $accountReportsDailyCounts] = $this->buildDailyReportSeries('profile', 7);

        $accountReportsReviewedCount = (int) (new Query())
            ->from(Denuncia::tableName())
            ->where([
                'target_type' => 'profile',
                'estado_revisao' => Denuncia::ESTADO_REVISAO_REVISTO,
            ])
            ->count();

        $accountReports = (new Query())
            ->select([
                'b.id',
                'b.target_user_id',
                'b.target_post_id',
                'b.target_type',
                'b.estado_revisao',
                'b.motivo',
                'b.descricao',
                'b.data_denuncia AS created_at',
                'u.username as reportado_username',
                'rep.username as reporter_username',
            ])
            ->from(['b' => Denuncia::tableName()])
            ->innerJoin(['u' => 'user'], 'u.id = b.target_user_id')
            ->leftJoin(['rep' => 'user'], 'rep.id = b.autor_id')
            ->where([
                'b.target_type' => 'profile',
                'b.estado_revisao' => Denuncia::ESTADO_REVISAO_PENDENTE,
            ])
            ->orderBy(['b.id' => SORT_DESC])
            ->all();

        if (Yii::$app->request->isAjax && (int) Yii::$app->request->get('refresh', 0) === 1) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $rows = array_map(static function (array $row): array {
                $reportadoUsername = trim((string) ($row['reportado_username'] ?? ''));
                $reporterUsername = trim((string) ($row['reporter_username'] ?? ''));

                return [
                    'id' => (int) ($row['id'] ?? 0),
                    'reportado' => $reportadoUsername !== '' ? $reportadoUsername : 'Utilizador removido',
                    'reportou' => $reporterUsername !== '' ? $reporterUsername : 'Sistema',
                    'review_url' => Url::to('/' . $reportadoUsername . '?rever=1'),
                    'mark_reviewed_url' => Url::to(['/reports/mark-account-report-reviewed', 'id' => (int) ($row['id'] ?? 0)]),
                ];
            }, $accountReports);

            return [
                'success' => true,
                'count' => count($rows),
                'rows' => $rows,
                'stats' => [
                    'pending' => count($rows),
                    'reviewed' => $accountReportsReviewedCount,
                ],
                'daily_labels' => $accountReportsDailyLabels,
                'daily_counts' => $accountReportsDailyCounts,
            ];
        }

        return $this->render('@app/views/user/default/reports-accounts', [
            'accountReports' => $accountReports,
            'accountReportsCount' => count($accountReports),
            'accountReportsReviewedCount' => $accountReportsReviewedCount,
            'accountReportsDailyLabels' => $accountReportsDailyLabels,
            'accountReportsDailyCounts' => $accountReportsDailyCounts,
        ]);
    }

    public function actionReportsContent()
    {
        if (!RolePermissionHelper::isCurrentUserAdmin()) {
            throw new NotFoundHttpException('Pagina nao encontrada.');
        }

        [$contentReportsDailyLabels, $contentReportsDailyCounts] = $this->buildDailyReportSeries('post', 7);

        $contentReportsReviewedCount = (int) (new Query())
            ->from(Denuncia::tableName())
            ->where([
                'target_type' => 'post',
                'estado_revisao' => Denuncia::ESTADO_REVISAO_REVISTO,
            ])
            ->count();

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

        if (Yii::$app->request->isAjax && (int) Yii::$app->request->get('refresh', 0) === 1) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $rows = array_map(static function (array $row): array {
                $reportadoUsername = trim((string) ($row['reportado_username'] ?? ''));
                $reporterUsername = trim((string) ($row['reporter_username'] ?? ''));
                $postId = (int) ($row['post_id'] ?? $row['target_post_id'] ?? 0);
                $postLabel = trim((string) ($row['titulo'] ?? ''));
                if ($postLabel === '') {
                    $postLabel = $postId > 0 ? ('Post #' . $postId) : 'Post removido';
                }

                return [
                    'report_id' => (int) ($row['report_id'] ?? 0),
                    'post_label' => $postLabel,
                    'post_url' => Url::to(['/homepage/post-aberto', 'id' => $postId, 'rever' => 1]),
                    'reportado' => $reportadoUsername !== '' ? $reportadoUsername : 'Utilizador removido',
                    'reportou' => $reporterUsername !== '' ? $reporterUsername : 'Utilizador removido',
                    'estado' => 'Pendente',
                    'mark_reviewed_url' => Url::to(['/reports/mark-post-report-reviewed', 'id' => (int) ($row['report_id'] ?? 0)]),
                ];
            }, $contentReports);

            return [
                'success' => true,
                'count' => count($rows),
                'rows' => $rows,
                'stats' => [
                    'pending' => count($rows),
                    'reviewed' => $contentReportsReviewedCount,
                ],
                'daily_labels' => $contentReportsDailyLabels,
                'daily_counts' => $contentReportsDailyCounts,
            ];
        }

        return $this->render('@app/views/user/default/reports-content', [
            'contentReports' => $contentReports,
            'contentReportsCount' => (int) (new Query())
                ->from(Denuncia::tableName())
                ->where([
                    'target_type' => 'post',
                    'estado_revisao' => Denuncia::ESTADO_REVISAO_PENDENTE,
                ])
                ->count(),
            'contentReportsReviewedCount' => $contentReportsReviewedCount,
            'contentReportsDailyLabels' => $contentReportsDailyLabels,
            'contentReportsDailyCounts' => $contentReportsDailyCounts,
        ]);
    }

    public function actionReportsEvents()
    {
        if (!RolePermissionHelper::isCurrentUserAdmin()) {
            throw new NotFoundHttpException('Pagina nao encontrada.');
        }

        [$eventReportsDailyLabels, $eventReportsDailyCounts] = $this->buildDailyReportSeries('event', 7);

        $eventReportsReviewedCount = (int) (new Query())
            ->from(Denuncia::tableName())
            ->where([
                'target_type' => 'event',
                'estado_revisao' => Denuncia::ESTADO_REVISAO_REVISTO,
            ])
            ->count();

        $eventReports = (new Query())
            ->select([
                'r.id AS report_id',
                'e.id AS event_id',
                'e.title',
                'r.target_event_id',
                'r.motivo',
                'r.descricao',
                'r.estado_revisao',
                'r.data_denuncia AS created_at',
                'u.username as reportado_username',
                'rep.username as reporter_username',
            ])
            ->from(['r' => Denuncia::tableName()])
            ->leftJoin(['e' => 'event'], 'e.id = r.target_event_id')
            ->leftJoin(['u' => 'user'], 'u.id = r.target_user_id')
            ->leftJoin(['rep' => 'user'], 'rep.id = r.autor_id')
            ->where([
                'r.target_type' => 'event',
                'r.estado_revisao' => Denuncia::ESTADO_REVISAO_PENDENTE,
            ])
            ->orderBy(['r.id' => SORT_DESC])
            ->all();

        if (Yii::$app->request->isAjax && (int) Yii::$app->request->get('refresh', 0) === 1) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $rows = array_map(static function (array $row): array {
                $reportadoUsername = trim((string) ($row['reportado_username'] ?? ''));
                $reporterUsername = trim((string) ($row['reporter_username'] ?? ''));
                $eventId = (int) ($row['event_id'] ?? $row['target_event_id'] ?? 0);
                $eventLabel = trim((string) ($row['title'] ?? ''));
                if ($eventLabel === '') {
                    $eventLabel = $eventId > 0 ? ('Evento #' . $eventId) : 'Evento removido';
                }

                return [
                    'report_id' => (int) ($row['report_id'] ?? 0),
                    'event_label' => $eventLabel,
                    'event_url' => Url::to(['/event/view', 'id' => $eventId]),
                    'reportado' => $reportadoUsername !== '' ? $reportadoUsername : 'Utilizador removido',
                    'reportou' => $reporterUsername !== '' ? $reporterUsername : 'Utilizador removido',
                    'estado' => 'Pendente',
                    'mark_reviewed_url' => Url::to(['/reports/mark-event-report-reviewed', 'id' => (int) ($row['report_id'] ?? 0)]),
                ];
            }, $eventReports);

            return [
                'success' => true,
                'count' => count($rows),
                'rows' => $rows,
                'stats' => [
                    'pending' => count($rows),
                    'reviewed' => $eventReportsReviewedCount,
                ],
                'daily_labels' => $eventReportsDailyLabels,
                'daily_counts' => $eventReportsDailyCounts,
            ];
        }

        return $this->render('@app/views/user/default/reports-events', [
            'eventReports' => $eventReports,
            'eventReportsCount' => (int) (new Query())
                ->from(Denuncia::tableName())
                ->where([
                    'target_type' => 'event',
                    'estado_revisao' => Denuncia::ESTADO_REVISAO_PENDENTE,
                ])
                ->count(),
            'eventReportsReviewedCount' => $eventReportsReviewedCount,
            'eventReportsDailyLabels' => $eventReportsDailyLabels,
            'eventReportsDailyCounts' => $eventReportsDailyCounts,
        ]);
    }

    private function buildDailyReportSeries(string $targetType, int $days): array
    {
        $days = max(1, $days);
        $startDate = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));

        $rows = (new Query())
            ->select([
                'dia' => 'DATE(data_denuncia)',
                'total' => 'COUNT(*)',
            ])
            ->from(Denuncia::tableName())
            ->where(['target_type' => $targetType])
            ->andWhere(['>=', 'data_denuncia', $startDate . ' 00:00:00'])
            ->groupBy(['DATE(data_denuncia)'])
            ->indexBy('dia')
            ->all();

        $labels = [];
        $counts = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $dateKey = date('Y-m-d', strtotime('-' . $i . ' days'));
            $labels[] = date('d/m', strtotime($dateKey));
            $counts[] = (int) ($rows[$dateKey]['total'] ?? 0);
        }

        return [$labels, $counts];
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

    public function actionMarkAccountReportReviewed($id)
    {
        if (!RolePermissionHelper::isCurrentUserAdmin()) {
            throw new NotFoundHttpException('Pagina nao encontrada.');
        }

        $reportId = (int) $id;
        if ($reportId <= 0) {
            throw new NotFoundHttpException('Report invalido.');
        }

        $report = Denuncia::findOne($reportId);
        if ($report === null || $report->target_type !== 'profile') {
            throw new NotFoundHttpException('Report de conta nao encontrado.');
        }

        if ($report->estado_revisao !== Denuncia::ESTADO_REVISAO_REVISTO) {
            $report->estado_revisao = Denuncia::ESTADO_REVISAO_REVISTO;
            if (!$report->save(false)) {
                Yii::$app->session->setFlash('Profile-error', 'Nao foi possivel atualizar o estado do report.');
            }
        }

        return $this->redirect(['/reports/reports-accounts']);
    }

    public function actionMarkEventReportReviewed($id)
    {
        if (!RolePermissionHelper::isCurrentUserAdmin()) {
            throw new NotFoundHttpException('Pagina nao encontrada.');
        }

        $reportId = (int) $id;
        if ($reportId <= 0) {
            throw new NotFoundHttpException('Report invalido.');
        }

        $report = Denuncia::findOne($reportId);
        if ($report === null || $report->target_type !== 'event') {
            throw new NotFoundHttpException('Report de evento nao encontrado.');
        }

        if ($report->estado_revisao !== Denuncia::ESTADO_REVISAO_REVISTO) {
            $report->estado_revisao = Denuncia::ESTADO_REVISAO_REVISTO;
            if (!$report->save(false)) {
                Yii::$app->session->setFlash('Profile-error', 'Nao foi possivel atualizar o estado do report.');
            }
        }

        return $this->redirect(['/reports/reports-events']);
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
