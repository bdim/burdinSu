<?php

namespace app\controllers;

use app\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\debug\models\timeline\DataProvider;
use yii\filters\AccessControl;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use app\models\Article;
use app\models\Files;
use dosamigos\editable\EditableAction;

class ArticleController extends Controller
{

    public $title;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index','list','update','add','delete','delete-file'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return User::isUserAdmin();
                        }
                    ]
                ],
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'update' => [
                'class' => EditableAction::className(),
                //'scenario' => 'editable',  //optional
                'modelClass' => Article::className(),
            ],
        ];
    }

    public function actionIndex(){

        $query = Article::find()->orderBy('publish_date');

        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'attributes' => [
                    'id',
                    'publish_date',
                ],
                'defaultOrder' => [
                    'publish_date' => SORT_ASC,
                ]
            ],
        ]);

        return $this->render('list', [
            'dataProvider' => $provider,
        ]);
    }

    public function actionList(){
        $query = Article::find();

        $filter   = Yii::$app->request->get();
        if (!empty($filter['year'])){
            $query->andwhere( 'YEAR(`publish_date`) = :year', [':year' => $filter['year']]);
        }

        $provider = new ActiveDataProvider   ([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'attributes' => [
                    'publish_date',
                    'id',
                    'date_start',
                    'date_end',
                    'title',
                ],
                'defaultOrder' => [
                    'publish_date' => SORT_DESC,
                ]
            ],
        ]);

        return $this->render('list', [
            'dataProvider' => $provider,
        ]);

    }

    public function actionAdd(){
        $model = new Article();
        $model->save();
        $this->redirect(Url::to(["article/list"]));
    }
}
