<?php

namespace app\controllers;

use app\helpers\RolePermissionHelper;
use app\models\PlanoNutricional;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class PlanController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['ver-plano'],
                        'allow' => true,
                        'roles' => ['?', '@'],
                    ],
                    [
                        'actions' => ['criar-plano', 'criar-plano-semanal', 'delete'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'criar-plano' => ['get'],
                    'criar-plano-semanal' => ['get', 'post'],
                    'ver-plano' => ['get'],
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    public function actionDelete($id)
    {
        if (Yii::$app->user->isGuest) {
            throw new ForbiddenHttpException('Nao tens permissao para eliminar planos.');
        }

        $plan = PlanoNutricional::findOne((int) $id);
        if ($plan === null) {
            throw new NotFoundHttpException('Plano nao encontrado.');
        }

        $currentUserId = (int) Yii::$app->user->id;
        if ((int) $plan->user_id !== $currentUserId) {
            throw new ForbiddenHttpException('Apenas o criador pode eliminar este plano.');
        }

        $imagePaths = $this->extractPlanImagePaths((string) $plan->estrutura_json);

        if ($plan->delete() !== false) {
            $this->deletePlanFiles($imagePaths);
            Yii::$app->session->setFlash('Plan-success', 'Plano eliminado com sucesso.');
        } else {
            Yii::$app->session->setFlash('Plan-error', 'Nao foi possivel eliminar o plano.');
        }

        return $this->redirect(['/perfil']);
    }

    public function actionCriarPlano()
    {
        if (Yii::$app->user->isGuest) {
            throw new ForbiddenHttpException('Nao tens permissao para criar planos.');
        }

        $currentUserId = (int) Yii::$app->user->id;

        if (!RolePermissionHelper::isUserNutritionist($currentUserId)) {
            throw new ForbiddenHttpException('Apenas nutricionistas podem criar planos.');
        }

        return $this->render('@app/views/user/default/criarplano');
    }

    public function actionCriarPlanoSemanal()
    {
        if (Yii::$app->user->isGuest) {
            throw new ForbiddenHttpException('Nao tens permissao para criar planos.');
        }

        $currentUserId = (int) Yii::$app->user->id;
        $nomePlano = trim((string) Yii::$app->request->get('nomePlano', ''));
        $imagemPlano = trim((string) Yii::$app->request->get('imagemPlano', ''));
        $selectedDay = trim((string) Yii::$app->request->get('selectedDay', '2ª'));

        if (!RolePermissionHelper::isUserNutritionist($currentUserId)) {
            throw new ForbiddenHttpException('Apenas nutricionistas podem criar planos.');
        }

        $session = Yii::$app->session;
        $sessionKey = 'plano_criacao';

        if (Yii::$app->request->isPost) {
            $selectedDay = trim((string) Yii::$app->request->post('diaSelecionado', $selectedDay));
            $nomePlano = trim((string) Yii::$app->request->post('nomePlano', $nomePlano));
            $imagemPlano = trim((string) Yii::$app->request->post('imagemPlano', $imagemPlano));

            $mealDescriptions = Yii::$app->request->post('mealDescriptions', []);
            if (empty($mealDescriptions)) {
                $uploadedCover = UploadedFile::getInstanceByName('planoImagem');
                if ($uploadedCover instanceof UploadedFile && $uploadedCover->error === UPLOAD_ERR_OK) {
                    $coverDir = Yii::getAlias('@webroot/uploads/planos/covers');
                    if (!is_dir($coverDir)) {
                        mkdir($coverDir, 0775, true);
                    }

                    $safeBaseName = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($uploadedCover->name, PATHINFO_FILENAME));
                    $safeBaseName = $safeBaseName ?: 'plano-capa';
                    $fileName = 'plano_capa_' . $currentUserId . '_' . time() . '_' . $safeBaseName . '.' . $uploadedCover->extension;
                    $fullPath = $coverDir . DIRECTORY_SEPARATOR . $fileName;

                    if ($uploadedCover->saveAs($fullPath)) {
                        $imagemPlano = 'uploads/planos/covers/' . $fileName;
                    }
                }

                $session->set($sessionKey, [
                    'nomePlano' => $nomePlano,
                    'imagemPlano' => $imagemPlano,
                ]);

                return $this->redirect([
                    '/criar-plano-semanal',
                    'nomePlano' => $nomePlano,
                    'imagemPlano' => $imagemPlano,
                    'selectedDay' => $selectedDay,
                ]);
            }

            $sessionData = (array) $session->get($sessionKey, []);
            $nomePlano = $nomePlano !== '' ? $nomePlano : trim((string) ($sessionData['nomePlano'] ?? ''));
            $imagemPlano = $imagemPlano !== '' ? $imagemPlano : trim((string) ($sessionData['imagemPlano'] ?? ''));

            $mealLabels = Yii::$app->request->post('mealLabels', []);
            $mealDays = Yii::$app->request->post('mealDays', []);
            $uploadedImages = UploadedFile::getInstancesByName('mealImages');

            $uploadDir = Yii::getAlias('@webroot/uploads/planos');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $mealsByDay = [];
            foreach ((array) $mealDescriptions as $index => $mealDescription) {
                $description = trim((string) $mealDescription);
                $label = trim((string) ($mealLabels[$index] ?? ''));
                $day = trim((string) ($mealDays[$index] ?? $selectedDay));
                $uploadedImage = $uploadedImages[$index] ?? null;

                $imagePath = null;
                if ($uploadedImage instanceof UploadedFile && $uploadedImage->error === UPLOAD_ERR_OK) {
                    $safeBaseName = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($uploadedImage->name, PATHINFO_FILENAME));
                    $safeBaseName = $safeBaseName ?: 'plano';
                    $fileName = 'plano_' . $currentUserId . '_' . time() . '_' . $index . '_' . $safeBaseName . '.' . $uploadedImage->extension;
                    $fullPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

                    if ($uploadedImage->saveAs($fullPath)) {
                        $imagePath = 'uploads/planos/' . $fileName;
                    }
                }

                if ($description === '' && $imagePath === null) {
                    continue;
                }

                if (!isset($mealsByDay[$day])) {
                    $mealsByDay[$day] = [];
                }

                $mealsByDay[$day][] = [
                    'label' => $label !== '' ? $label : (($index + 1) . 'º Refeição'),
                    'description' => $description,
                    'image' => $imagePath,
                ];
            }

            $canonicalDays = ['2ª', '3ª', '4ª', '5ª', '6ª', 'Sa', 'Do'];
            $orderedMealsByDay = [];
            foreach ($canonicalDays as $canonicalDay) {
                if (!isset($mealsByDay[$canonicalDay]) || !is_array($mealsByDay[$canonicalDay])) {
                    continue;
                }

                $orderedMealsByDay[$canonicalDay] = array_values(array_filter($mealsByDay[$canonicalDay], 'is_array'));
            }

            $mealsByDay = $orderedMealsByDay;

            foreach ($canonicalDays as $canonicalDay) {
                if (isset($mealsByDay[$canonicalDay]) && empty($mealsByDay[$canonicalDay])) {
                    unset($mealsByDay[$canonicalDay]);
                }
            }

            if (isset($mealsByDay['Do']) && is_array($mealsByDay['Do'])) {
                $prefixMeals = [];
                foreach (['2ª', '3ª', '4ª', '5ª', '6ª', 'Sa'] as $otherDay) {
                    if (isset($mealsByDay[$otherDay]) && is_array($mealsByDay[$otherDay])) {
                        foreach ($mealsByDay[$otherDay] as $meal) {
                            if (is_array($meal)) {
                                $prefixMeals[] = $meal;
                            }
                        }
                    }
                }

                $sundayMeals = array_values($mealsByDay['Do']);
                $prefixCount = count($prefixMeals);

                if ($prefixCount > 0 && count($sundayMeals) > $prefixCount) {
                    $prefixMatches = true;

                    for ($i = 0; $i < $prefixCount; $i++) {
                        $leftMeal = $prefixMeals[$i] ?? null;
                        $rightMeal = $sundayMeals[$i] ?? null;

                        $leftFingerprint = is_array($leftMeal)
                            ? json_encode([
                                'label' => (string) ($leftMeal['label'] ?? ''),
                                'description' => (string) ($leftMeal['description'] ?? ''),
                                'image' => (string) ($leftMeal['image'] ?? ''),
                            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                            : null;
                        $rightFingerprint = is_array($rightMeal)
                            ? json_encode([
                                'label' => (string) ($rightMeal['label'] ?? ''),
                                'description' => (string) ($rightMeal['description'] ?? ''),
                                'image' => (string) ($rightMeal['image'] ?? ''),
                            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                            : null;

                        if ($leftFingerprint === null || $rightFingerprint === null || $leftFingerprint !== $rightFingerprint) {
                            $prefixMatches = false;
                            break;
                        }
                    }

                    if ($prefixMatches) {
                        $mealsByDay['Do'] = array_slice($sundayMeals, $prefixCount);
                    }
                }
            }

            $plan = new PlanoNutricional();
            $plan->user_id = $currentUserId;
            $plan->titulo = $nomePlano !== '' ? $nomePlano : 'Plano alimentar';
            $plan->objetivo = 'Plano semanal';
            $plan->descricao = 'Plano alimentar criado com refeições por dia';
            $plan->estrutura_json = json_encode([
                'nomePlano' => $nomePlano,
                'imagemPlano' => $imagemPlano,
                'dias' => $mealsByDay,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            if ($plan->save()) {
                $session->remove($sessionKey);
                Yii::$app->session->setFlash('Plan-success', 'Plano nutricional criado com sucesso.');
                return $this->redirect(Yii::$app->request->referrer ?: ['/perfil']);
            }

            Yii::$app->session->setFlash('Plan-error', implode(' | ', $plan->getFirstErrors()));
        }

        return $this->render('@app/views/user/default/planosemanal', [
            'selectedDay' => $selectedDay,
            'nomePlano' => $nomePlano,
            'imagemPlano' => $imagemPlano,
            'plan' => new PlanoNutricional(),
        ]);
    }

    public function actionVerPlano($id)
    {
        $plan = PlanoNutricional::findOne((int) $id);

        if ($plan === null) {
            throw new NotFoundHttpException('Plano nao encontrado.');
        }

        $structure = $this->decodePlanStructure((string) $plan->estrutura_json);
        $diasRaw = isset($structure['dias']) && is_array($structure['dias']) ? $structure['dias'] : [];
        $dias = $this->buildPlanDays($diasRaw);
        $dias = $this->trimDuplicatedSundayPrefix($dias);

        $nomePlanoEstrutura = trim((string) ($structure['nomePlano'] ?? ''));
        $tituloPlano = $nomePlanoEstrutura !== '' ? $nomePlanoEstrutura : trim((string) $plan->titulo);
        if ($tituloPlano === '') {
            $tituloPlano = 'Plano alimentar';
        }

        $imagemPlano = trim((string) ($structure['imagemPlano'] ?? ''));
        $autorUsername = $this->resolveAuthorUsername((int) $plan->user_id);

        return $this->render('@app/views/user/default/plano-aberto', [
            'plan' => $plan,
            'tituloPlano' => $tituloPlano,
            'dias' => $dias,
            'imagemPlano' => $imagemPlano,
            'autorUsername' => $autorUsername,
        ]);
    }

    private function decodePlanStructure(string $json): array
    {
        $structure = json_decode($json, true);
        return is_array($structure) ? $structure : [];
    }

    private function buildPlanDays(array $diasRaw): array
    {
        $dias = [];
        foreach ($diasRaw as $dayKey => $dayMeals) {
            if (!is_array($dayMeals)) {
                continue;
            }

            $dias[trim((string) $dayKey)] = $dayMeals;
        }

        return $dias;
    }

    private function trimDuplicatedSundayPrefix(array $dias): array
    {
        if (!isset($dias['Do']) || !is_array($dias['Do'])) {
            return $dias;
        }

        $prefixMeals = [];
        foreach (['2ª', '3ª', '4ª', '5ª', '6ª', 'Sa'] as $otherDay) {
            if (!isset($dias[$otherDay]) || !is_array($dias[$otherDay])) {
                continue;
            }

            foreach ($dias[$otherDay] as $meal) {
                if (is_array($meal)) {
                    $prefixMeals[] = $meal;
                }
            }
        }

        $sundayMeals = array_values($dias['Do']);
        $prefixCount = count($prefixMeals);

        if ($prefixCount === 0 || count($sundayMeals) <= $prefixCount) {
            return $dias;
        }

        for ($i = 0; $i < $prefixCount; $i++) {
            $leftMeal = $prefixMeals[$i] ?? null;
            $rightMeal = $sundayMeals[$i] ?? null;

            $leftFingerprint = is_array($leftMeal)
                ? json_encode([
                    'label' => (string) ($leftMeal['label'] ?? ''),
                    'description' => (string) ($leftMeal['description'] ?? ''),
                    'image' => (string) ($leftMeal['image'] ?? ''),
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : null;
            $rightFingerprint = is_array($rightMeal)
                ? json_encode([
                    'label' => (string) ($rightMeal['label'] ?? ''),
                    'description' => (string) ($rightMeal['description'] ?? ''),
                    'image' => (string) ($rightMeal['image'] ?? ''),
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : null;

            if ($leftFingerprint === null || $rightFingerprint === null || $leftFingerprint !== $rightFingerprint) {
                return $dias;
            }
        }

        $dias['Do'] = array_slice($sundayMeals, $prefixCount);
        return $dias;
    }

    private function resolveAuthorUsername(int $userId): string
    {
        $userModule = Yii::$app->getModule('user');
        $userClass = $userModule->model('User');

        if (is_subclass_of($userClass, ActiveRecord::class)) {
            $autor = $userClass::find()
                ->select(['username'])
                ->where(['id' => $userId])
                ->asArray()
                ->one();
        } else {
            $autor = (new Query())
                ->from($userClass::tableName())
                ->where(['id' => $userId])
                ->one();
        }

        return (string) ($autor['username'] ?? 'utilizador');
    }

    private function extractPlanImagePaths(string $estruturaJson): array
    {
        $paths = [];
        $structure = $this->decodePlanStructure($estruturaJson);

        $coverPath = trim((string) ($structure['imagemPlano'] ?? ''));
        if ($coverPath !== '') {
            $paths[] = $coverPath;
        }

        $dias = isset($structure['dias']) && is_array($structure['dias']) ? $structure['dias'] : [];
        foreach ($dias as $refeicoes) {
            if (!is_array($refeicoes)) {
                continue;
            }

            foreach ($refeicoes as $refeicao) {
                if (!is_array($refeicao)) {
                    continue;
                }

                $imagePath = trim((string) ($refeicao['image'] ?? ''));
                if ($imagePath !== '') {
                    $paths[] = $imagePath;
                }
            }
        }

        return array_values(array_unique($paths));
    }

    private function deletePlanFiles(array $relativePaths): void
    {
        $webroot = rtrim(Yii::getAlias('@webroot'), DIRECTORY_SEPARATOR);

        foreach ($relativePaths as $relativePath) {
            $cleanPath = ltrim((string) $relativePath, '/\\');
            if ($cleanPath === '') {
                continue;
            }

            $absolutePath = $webroot . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $cleanPath);
            if (!is_file($absolutePath)) {
                continue;
            }

            $resolved = realpath($absolutePath);
            if ($resolved === false || strpos($resolved, $webroot . DIRECTORY_SEPARATOR) !== 0) {
                continue;
            }

            @unlink($resolved);
        }
    }
}
