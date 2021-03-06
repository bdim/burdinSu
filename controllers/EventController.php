<?php

namespace app\controllers;

use app\models\Blog;
use app\models\Files;
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
use app\models\Event;
use dosamigos\editable\EditableAction;

class EventController extends Controller
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
                        'actions' => ['album','list','update','add','delete','delete-file'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return User::isUserAdmin();
                        }
                    ],
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
                'modelClass' => Event::className(),
            ],
        ];
    }

    public function actionAdd(){
        $model = new Event();
        $model->save();
        $this->redirect(Url::to(["event/list"]));
    }

    public function actionDelete($id){
        Event::deleteAll(['id' => $id]);

        if (Yii::$app->request->isAjax)
            return $this->actionList();
        else
            $this->redirect(Url::to(['event/list']));
    }

    public function actionList(){
        $query = Event::find();

        $filter   = Yii::$app->request->get();
        if (!empty($filter['year'])){
                $query->andwhere( 'YEAR(`date_start`) = :year', [':year' => $filter['year']]);
        }

        $provider = new ActiveDataProvider([
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

    public function actionDeleteFile($id){
        Files::deleteAll(['id' => $id]);

        if (Yii::$app->request->isAjax)
            return $this->actionList();
        else
            $this->redirect(Url::to(['event/list']));
    }

}
