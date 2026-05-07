<?php

namespace app\controllers;

use app\helpers\RolePermissionHelper;
use app\models\PlanoNutricional;
use app\models\PlanoHasTag;
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
    public function beforeAction($action)
    {
        if ($action->id === 'criar-plano-semanal' && $this->isLikelyPostSizeOverflow()) {
            // In this specific overflow case PHP drops POST/FILES entirely, so CSRF body token is unavailable.
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

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
                        'actions' => ['criar-plano', 'criar-plano-semanal', 'editar', 'delete'],
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
                    'editar' => ['get'],
                    'ver-plano' => ['get'],
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    public function actionEditar($id)
    {
        if (Yii::$app->user->isGuest) {
            throw new ForbiddenHttpException('Nao tens permissao para editar planos.');
        }

        $plan = PlanoNutricional::findOne((int) $id);
        if ($plan === null) {
            throw new NotFoundHttpException('Plano nao encontrado.');
        }

        $currentUserId = (int) Yii::$app->user->id;
        if ((int) $plan->user_id !== $currentUserId) {
            throw new ForbiddenHttpException('Apenas o criador pode editar este plano.');
        }

        $structure = $this->decodePlanStructure((string) $plan->estrutura_json);
        $nomePlano = trim((string) ($structure['nomePlano'] ?? $plan->titulo ?? ''));
        $imagemPlano = trim((string) ($structure['imagemPlano'] ?? ''));
        $selectedDay = trim((string) Yii::$app->request->get('selectedDay', '2ª'));
        $diasRaw = isset($structure['dias']) && is_array($structure['dias']) ? $structure['dias'] : [];

        $canonicalDays = ['2ª', '3ª', '4ª', '5ª', '6ª', 'Sa', 'Do'];
        $initialMealsByDay = [];
        foreach ($canonicalDays as $day) {
            $initialMealsByDay[$day] = [];
            $meals = isset($diasRaw[$day]) && is_array($diasRaw[$day]) ? $diasRaw[$day] : [];

            foreach ($meals as $meal) {
                if (!is_array($meal)) {
                    continue;
                }

                $imagePath = trim((string) ($meal['image'] ?? ''));
                $initialMealsByDay[$day][] = [
                    'label' => trim((string) ($meal['label'] ?? '')),
                    'description' => trim((string) ($meal['description'] ?? '')),
                    'imagePath' => $imagePath,
                    'imageUrl' => $imagePath !== '' ? Yii::getAlias('@web/' . ltrim($imagePath, '/')) : null,
                ];
            }
        }

        $selectedTagIds = [];
        foreach ($plan->tags as $tag) {
            $selectedTagIds[] = (int) $tag->id;
        }

        return $this->render('@app/views/user/default/planosemanal', [
            'selectedDay' => $selectedDay,
            'nomePlano' => $nomePlano,
            'imagemPlano' => $imagemPlano,
            'planId' => (int) $plan->id,
            'initialMealsByDay' => $initialMealsByDay,
            'plan' => $plan,
            'selectedTagIds' => $selectedTagIds,
        ]);
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

        if ($this->isLikelyPostSizeOverflow()) {
            Yii::$app->session->setFlash('Plan-error', 'O upload excedeu o limite permitido pelo servidor. Reduz o tamanho/quantidade das imagens e tenta novamente.');
            return $this->redirect(Yii::$app->request->referrer ?: ['/perfil']);
        }

        $currentUserId = (int) Yii::$app->user->id;
        $nomePlano = trim((string) Yii::$app->request->get('nomePlano', ''));
        $imagemPlano = trim((string) Yii::$app->request->get('imagemPlano', ''));
        $selectedDay = trim((string) Yii::$app->request->get('selectedDay', '2ª'));
        $planId = (int) Yii::$app->request->get('planId', 0);

        if (!RolePermissionHelper::isUserNutritionist($currentUserId)) {
            throw new ForbiddenHttpException('Apenas nutricionistas podem criar planos.');
        }

        $session = Yii::$app->session;
        $sessionKey = 'plano_criacao';

        if (Yii::$app->request->isPost) {
            $selectedDay = trim((string) Yii::$app->request->post('diaSelecionado', $selectedDay));
            $nomePlano = trim((string) Yii::$app->request->post('nomePlano', $nomePlano));
            $imagemPlano = trim((string) Yii::$app->request->post('imagemPlano', $imagemPlano));
            $planId = (int) Yii::$app->request->post('planId', $planId);

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
                    'planoTags' => (array) Yii::$app->request->post('planoTags', []),
                ]);

                return $this->redirect([
                    '/criar-plano-semanal',
                    'nomePlano' => $nomePlano,
                    'imagemPlano' => $imagemPlano,
                    'planId' => $planId,
                    'selectedDay' => $selectedDay,
                ]);
            }

            $sessionData = (array) $session->get($sessionKey, []);
            $nomePlano = $nomePlano !== '' ? $nomePlano : trim((string) ($sessionData['nomePlano'] ?? ''));
            $imagemPlano = $imagemPlano !== '' ? $imagemPlano : trim((string) ($sessionData['imagemPlano'] ?? ''));

            $mealLabels = Yii::$app->request->post('mealLabels', []);
            $mealDays = Yii::$app->request->post('mealDays', []);
            $mealExistingImages = Yii::$app->request->post('mealExistingImages', []);

            $uploadDir = Yii::getAlias('@webroot/uploads/planos');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $mealsByDay = [];
            foreach ((array) $mealDescriptions as $index => $mealDescription) {
                $description = trim((string) $mealDescription);
                $label = trim((string) ($mealLabels[$index] ?? ''));
                $day = trim((string) ($mealDays[$index] ?? $selectedDay));
                $existingImagePath = trim((string) ($mealExistingImages[$index] ?? ''));
                $uploadedImage = UploadedFile::getInstanceByName('mealImages[' . $index . ']');

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

                if ($imagePath === null && $existingImagePath !== '') {
                    $imagePath = $existingImagePath;
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

            $isEditing = $planId > 0;
            if ($isEditing) {
                $plan = PlanoNutricional::findOne($planId);
                if ($plan === null) {
                    throw new NotFoundHttpException('Plano nao encontrado.');
                }
                if ((int) $plan->user_id !== $currentUserId) {
                    throw new ForbiddenHttpException('Apenas o criador pode editar este plano.');
                }
            } else {
                $plan = new PlanoNutricional();
                $plan->user_id = $currentUserId;
            }

            $plan->titulo = $nomePlano !== '' ? $nomePlano : 'Plano alimentar';
            $plan->objetivo = 'Plano semanal';
            $plan->descricao = 'Plano alimentar criado com refeições por dia';
            $plan->estrutura_json = json_encode([
                'nomePlano' => $nomePlano,
                'imagemPlano' => $imagemPlano,
                'dias' => $mealsByDay,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            if ($plan->save()) {
                // Salvar tags do plano
                $selectedTags = Yii::$app->request->post('planoTags', []);
                $this->savePlanTags($plan->id, $selectedTags);

                $session->remove($sessionKey);
                Yii::$app->session->setFlash('Plan-success', $isEditing
                    ? 'Plano nutricional atualizado com sucesso.'
                    : 'Plano nutricional criado com sucesso.');

                if ($isEditing) {
                    return $this->redirect(['/plan/ver-plano', 'id' => (int) $plan->id]);
                }

                return $this->redirect(Yii::$app->request->referrer ?: ['/perfil']);
            }

            Yii::$app->session->setFlash('Plan-error', implode(' | ', $plan->getFirstErrors()));
        }

        return $this->render('@app/views/user/default/planosemanal', [
            'selectedDay' => $selectedDay,
            'nomePlano' => $nomePlano,
            'imagemPlano' => $imagemPlano,
            'planId' => $planId,
            'initialMealsByDay' => [],
            'plan' => new PlanoNutricional(),
        ]);
    }

    /**
     * Save plan tags
     *
     * @param integer $planId
     * @param array $tagIds
     */
    private function savePlanTags($planId, $tagIds)
    {
        // Delete existing tags
        PlanoHasTag::deleteAll(['plano_id' => $planId]);

        // Add new tags
        if (!empty($tagIds)) {
            foreach ($tagIds as $tagId) {
                $planTag = new PlanoHasTag();
                $planTag->plano_id = $planId;
                $planTag->tag_id = (int) $tagId;
                $planTag->save();
            }
        }
    }

    private function isLikelyPostSizeOverflow(): bool
    {
        $request = Yii::$app->request;
        if (!$request->isPost) {
            return false;
        }

        $contentLength = (int) ($request->headers->get('Content-Length', 0) ?: 0);
        if ($contentLength <= 0) {
            return false;
        }

        if (!empty($_POST) || !empty($_FILES)) {
            return false;
        }

        $postMaxBytes = $this->toBytes((string) ini_get('post_max_size'));
        if ($postMaxBytes > 0 && $contentLength > $postMaxBytes) {
            return true;
        }

        $uploadMaxBytes = $this->toBytes((string) ini_get('upload_max_filesize'));
        return $uploadMaxBytes > 0 && $contentLength > $uploadMaxBytes;
    }

    private function toBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        switch ($unit) {
            case 'g':
                $number *= 1024;
                // no break
            case 'm':
                $number *= 1024;
                // no break
            case 'k':
                $number *= 1024;
                break;
            default:
                if (ctype_alpha($unit)) {
                    return 0;
                }
        }

        return (int) round($number);
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
