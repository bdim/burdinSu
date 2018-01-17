<?php

namespace app\controllers;

use app\models\Blog;
use app\models\Log;
use app\models\Taxonomy;
use app\models\TelegramBot;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\debug\models\timeline\DataProvider;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\User;
use app\models\Files;
use app\models\SignupForm;
use yii\helpers\Url;
use app\components\VarDump;


class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
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
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {

/*        if (Yii::$app->user->isGuest) {
            $this->redirect(Url::to(['site/login']));
	    } else*/
/*            if (Yii::$app->user->identity->role == User::ROLE_ADMIN)
            $this->redirect(Url::to(['user/list']));*/
/*        elseif (Yii::$app->user->identity->role == User::ROLE_USER)
            $this->redirect(Url::to(['site/call-list']));*/



        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goHome();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionTelegramCode(){
        $code = rand(100,999);
        Yii::$app->cache->set("telegram-login-".$code, -1 , 60);
        $bot = new TelegramBot();
        $bot->executeCommands([TelegramBot::COMMAND_LOGIN]);

        echo $code;
    }
    public function actionTelegramLogin($code){
        $login = false;
        while (!$login) {
            $c = Yii::$app->cache->get("telegram-login-".$code);
            if (intval($c) > 0){
                $user = User::findById(intval($c));
                if (!empty($user))
                    Yii::$app->user->login($user, 3600*24*30);
            }

            if (intval($c) != -1)
                $login = true;
            else
                sleep(1);
        }
    }
    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }


    public function actionAddadmin() {
        $model = User::find()->where(['username' => 'admin'])->one();
        if (empty($model)) {
            $user = new User();
            $user->username = 'admin';
            $user->fio = 'admin';
            $user->info = 'Administrator';
            $user->role = User::ROLE_ADMIN;
            $user->setPassword('000');
            $user->generateAuthKey();
            if ($user->save()) {
                echo 'good';
            }
        }
    }

   /* public function actionSignup()
    {
        $model = new SignupForm();
 
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }
 
        return $this->render('signup', [
            'model' => $model,
        ]);
    }*/


    public function actionImport(){
        die;
        $content = file_get_contents('../upload/node-export-4.export');
        $content = json_decode($content);

        $i=0;
        foreach($content as $node){
            if (empty($node->body->und[0]->value)){
                VarDumper::dump($node,10,1);die;
            }
            $item = [
                'created_at' => date('Y-m-d H:i:s',$node->created),
                'user_id' => $node->uid,
                'title' => $node->title,
                'body'  => $node->body->und[0]->value,
                'publish_date'  => !empty($node->field_date->und[0]->value) ? $node->field_date->und[0]->value : date('Y-m-d H:i:s',$node->created),
                'tag'  => $node->field_tag->und[0]->tid ? $node->field_tag->und[0]->tid : Taxonomy::TAG_ARSENY,
            ];
            $keyword = [];
            if (is_array($node->field_keyword->und))
            foreach($node->field_keyword->und as $kw){
                $keyword[] = $kw->tid;
            }

            Blog::add($item,$keyword);
            $i++;
        }
        echo "added ".$i;
	}

    public function actionImportfoto(){
        if (!Yii::$app->user->isGuest) {
            $format = null;
            /*$format = [
                'pattern' => "/([0-9]{4})-([0-9]{2})-([0-9]{2})_([0-9]{2})-([0-9]{2})-([0-9]{2})/",
                'date' => [
                    'y' =>1,
                    'm' =>2,
                    'd' =>3,
                    'h' =>4,
                    'i' =>5,
                    's' =>6
                ]

            ];*/
            Files::importFilesFromFolder('photo_jpg',Files::TYPE_PHOTO, true, $format);
            Blog::flushCache();
            echo 'ok';
        }
    }

    public function actionImportAudio(){
        if (!Yii::$app->user->isGuest) {
            Files::importFilesFromFolder('audio', Files::TYPE_AUDIO);
            Blog::flushCache();
            echo 'ok';
        }
    }

    public function  actionTest(){
        /*$key = 'test';
        $data = Yii::$app->cache->getOrSet($key, function () {
            return 'test '.date('H:i:s');
        }, 600);

        VarDumper::dump($data,10,1);*/

/*        $t = Taxonomy::getIdByName('прогулка');
        VarDumper::dump($t,10,1);*/

/*        $q = Yii::$app->db->createCommand('SELECT DATE(`publish_date`) FROM {{%blog}} GROUP BY DATE(`publish_date`) '
        )->execute();*/

        //VarDumper::dump(Yii::$app->user->identity,10,1);

       // TelegramBot::sendEventMessage();

        /*$data = file_get_contents('https://api.telegram.org/file/bot387788348:AAHBcxXi9NkxhJz0LIrQku39M1E70DdzIAY/voice/file_57');
        //file_put_contents(UPLOAD_PATH.'/audio/test.ogg', $data, FILE_BINARY);
        $mimeType = $data->message->voice->mime_type;
        Files::add(['path' => 'photo/test.ogg', 'type_id' => Files::TYPE_AUDIO, 'params' => Json::encode(['mime-type' => $mimeType])]);*/

        //var_dump(Yii::$app->params['devicedetect']);
    }

    public function actionFlushblog(){
        if (!Yii::$app->user->isGuest) {
            Blog::clear();
            Blog::flushCache();
            echo 'Flushblog';
        }
    }
}
