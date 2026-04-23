<?php

namespace app\controllers;

use app\models\Agua;
use app\models\Perfil;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

class GotinhaController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'index' => ['get', 'post'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $userId = (int) Yii::$app->user->id;
        $profile = Perfil::findOne(['user_id' => $userId]);
        $dailyGoalMl = (int) ($profile->meta_diaria_agua ?? 2000);

        if (Yii::$app->request->isPost) {
            $novaMeta = (int) Yii::$app->request->post('nova_meta_ml', 0);
            if ($novaMeta > 0) {
                if ($profile === null) {
                    Yii::$app->session->setFlash('Agua-error', 'Nao foi possivel atualizar a meta porque o perfil nao existe.');
                    return $this->refresh();
                }

                $profile->meta_diaria_agua = $novaMeta;

                if ($profile->save(false, ['meta_diaria_agua'])) {
                    Yii::$app->session->setFlash('Agua-success', 'Meta diária atualizada com sucesso!');
                    return $this->refresh();
                }

                Yii::$app->session->setFlash('Agua-error', 'Erro ao atualizar a meta diaria.');
                return $this->refresh();
            }

            $quantidade = (int) Yii::$app->request->post('quantidade_ml', 0);
            if ($quantidade > 0) {
                $agua = new Agua();
                $agua->user_id = $userId;
                $agua->quantidade_ml = $quantidade;
                $agua->data_registo = date('Y-m-d H:i:s');

                if ($agua->save()) {
                    Yii::$app->session->setFlash('Agua-success', 'Agua registada com sucesso!');
                    return $this->refresh();
                }

                Yii::$app->session->setFlash('Agua-error', 'Erro ao registar agua.');
            } else {
                Yii::$app->session->setFlash('Agua-error', 'Quantidade deve ser maior que 0.');
            }
        }

        if ($profile !== null) {
            $dailyGoalMl = (int) ($profile->meta_diaria_agua ?? 2000);
        }

        $todayTotalMl = Agua::getTodayTotal($userId);
        $progressPercent = $dailyGoalMl > 0 ? min(100, (int) (($todayTotalMl / $dailyGoalMl) * 100)) : 0;
        $recentEntries = Agua::getRecentEntries($userId, 10);

        return $this->render('@app/views/user/default/gotinha', [
            'todayTotalMl' => $todayTotalMl,
            'dailyGoalMl' => $dailyGoalMl,
            'progressPercent' => $progressPercent,
            'recentEntries' => $recentEntries,
        ]);
    }
}