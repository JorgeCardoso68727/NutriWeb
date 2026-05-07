<?php

namespace app\controllers;

use Yii;
use app\models\Recipe;
use app\models\RecipeTag;
use app\models\RecipeHasTag;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * RecipeController implements the CRUD actions for Recipe model.
 */
class RecipeController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Recipe models with advanced filters.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $query = Recipe::find()->where(['status' => 'published']);

        // Filter by tags
        $selectedTags = Yii::$app->request->get('tags', []);
        if (!is_array($selectedTags)) {
            $selectedTags = [];
        }

        if (!empty($selectedTags)) {
            $query->joinWith(['recipeHasTags'])
                ->andWhere(['in', 'recipe_has_tag.tag_id', $selectedTags])
                ->groupBy('recipe.id')
                ->having('COUNT(*) = ' . count($selectedTags));
        }

        // Filter by difficulty
        if (Yii::$app->request->get('difficulty')) {
            $query->andWhere(['difficulty' => Yii::$app->request->get('difficulty')]);
        }

        // Filter by prep time
        if (Yii::$app->request->get('max_prep_time')) {
            $query->andWhere(['<=', 'prep_time', Yii::$app->request->get('max_prep_time')]);
        }

        // Filter by total time
        if (Yii::$app->request->get('max_total_time')) {
            $maxTime = Yii::$app->request->get('max_total_time');
            $query->andWhere([
                '<=',
                new \yii\db\Expression('COALESCE(prep_time, 0) + COALESCE(cook_time, 0)'),
                $maxTime
            ]);
        }

        // Search by title or description
        if (Yii::$app->request->get('search')) {
            $search = Yii::$app->request->get('search');
            $query->andWhere([
                'or',
                ['like', 'title', $search],
                ['like', 'description', $search]
            ]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query->with(['creator', 'tags']),
            'pagination' => [
                'pageSize' => 12,
            ],
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'title',
                    'prep_time',
                    'difficulty',
                    'created_at',
                ]
            ],
        ]);

        $tags = RecipeTag::find()->all();
        $difficulties = ['easy', 'medium', 'hard'];

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'tags' => $tags,
            'difficulties' => $difficulties,
            'selectedTags' => $selectedTags,
            'filterModel' => [
                'search' => Yii::$app->request->get('search'),
                'difficulty' => Yii::$app->request->get('difficulty'),
                'max_prep_time' => Yii::$app->request->get('max_prep_time'),
                'max_total_time' => Yii::$app->request->get('max_total_time'),
            ],
        ]);
    }

    /**
     * Displays a single Recipe model.
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Recipe model.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Recipe();
        $model->creator_id = Yii::$app->user->id;
        $model->status = 'draft';

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                // Save tags
                $selectedTags = Yii::$app->request->post('tags', []);
                $this->saveRecipeTags($model->id, $selectedTags);

                Yii::$app->session->setFlash('success', 'Receita criada com sucesso!');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        $tags = RecipeTag::find()->all();
        $difficulties = ['easy' => 'Fácil', 'medium' => 'Médio', 'hard' => 'Difícil'];

        return $this->render('create', [
            'model' => $model,
            'tags' => $tags,
            'difficulties' => $difficulties,
        ]);
    }

    /**
     * Updates an existing Recipe model.
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Check if user is the creator
        if ($model->creator_id != Yii::$app->user->id && !Yii::$app->user->identity->isAdmin()) {
            throw new \yii\web\ForbiddenHttpException('Você não tem permissão para editar esta receita.');
        }

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                // Save tags
                $selectedTags = Yii::$app->request->post('tags', []);
                $this->saveRecipeTags($model->id, $selectedTags);

                Yii::$app->session->setFlash('success', 'Receita atualizada com sucesso!');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        $tags = RecipeTag::find()->all();
        $difficulties = ['easy' => 'Fácil', 'medium' => 'Médio', 'hard' => 'Difícil'];
        $selectedTags = $model->getTags()->select('recipe_tag.id')->column();

        return $this->render('update', [
            'model' => $model,
            'tags' => $tags,
            'difficulties' => $difficulties,
            'selectedTags' => $selectedTags,
        ]);
    }

    /**
     * Publishes a recipe.
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionPublish($id)
    {
        $model = $this->findModel($id);

        if ($model->creator_id != Yii::$app->user->id && !Yii::$app->user->identity->isAdmin()) {
            throw new \yii\web\ForbiddenHttpException('Você não tem permissão para publicar esta receita.');
        }

        $model->status = 'published';
        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'Receita publicada com sucesso!');
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Deletes an existing Recipe model.
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->creator_id != Yii::$app->user->id && !Yii::$app->user->identity->isAdmin()) {
            throw new \yii\web\ForbiddenHttpException('Você não tem permissão para eliminar esta receita.');
        }

        $model->delete();

        return $this->redirect(['index']);
    }

    /**
     * Save recipe tags
     *
     * @param integer $recipeId
     * @param array $tagIds
     */
    protected function saveRecipeTags($recipeId, $tagIds)
    {
        // Delete existing tags
        RecipeHasTag::deleteAll(['recipe_id' => $recipeId]);

        // Add new tags
        foreach ($tagIds as $tagId) {
            $recipeTag = new RecipeHasTag();
            $recipeTag->recipe_id = $recipeId;
            $recipeTag->tag_id = $tagId;
            $recipeTag->save();
        }
    }

    /**
     * Finds the Recipe model based on its primary key value.
     *
     * @param integer $id
     * @return Recipe the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Recipe::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Página não encontrada.');
    }
}
