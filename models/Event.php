<?php     	
    namespace app\models;
     
    use app\components\RenderCache;
    use app\components\StringUtils;
    use app\components\TaxonomyBehavior;
    use Yii;
    use yii\base\NotSupportedException;
    use yii\behaviors\TimestampBehavior;
    use yii\db\ActiveRecord;
    use yii\helpers\Json;
    use yii\helpers\VarDumper;
    use yii\web\IdentityInterface;
    use yii\web\UrlManager;
    use app\models\Taxonomy;
    use yii\caching\TagDependency;

    /**
     * Event model
     */
    class Event extends ActiveRecord
    {

       // public $pub_date;

        /**
         * внешняя ссылка на видео
         */
        public $ext_video;


        /**
         * @inheritdoc
         */
        public static function tableName()
        {
            return '{{%event}}';
        }
     

        /**
         * @inheritdoc
         */
        public function rules()
        {
            return [
                [['date_start', 'date_end', 'publish_date', 'user_id', 'title', 'body', 'teaser'], 'safe' ],
            ];
        }

        /**
         * @inheritdoc
         */
        public function behaviors()
        {
            return [
                TaxonomyBehavior::className()
            ];
        }

        /**
         * @inheritdoc
         */
        public static function findIdentity($id)
        {
            return static::findOne(['id' => $id]);
        }

        public function beforeSave($insert){

            if (empty($this->user_id))
                $this->user_id = Yii::$app->user->id;

            if (is_null($this->publish_date))
                $this->publish_date = date("Y:m:d H:i:s");

            return parent::beforeSave($insert);
        }

        public function afterSave($insert, $changedAttributes){
            Blog::flushCache();
            return parent::afterSave($insert, $changedAttributes);
        }

        public static function postEvent(){
            $date = date('Y-m-d', time() - 24*3600);
            return static::findOne(['date_end' => $date]);
        }

        public function getMessage(){
            $message = $this->teaser ? 'Привет! '.$this->teaser.'. ' : '';
            return $message;
        }

        /* relation User */
        public function getUser(){
            return $this->hasOne(User::className(), ['id' => 'user_id']);
        }


        /* кеш */
        public static function getCacheDependency(){
            return RenderCache::getCacheDependency('event');
        }

        public static function flushCache(){
            RenderCache::flushCache('event');
        }

        public static function getItemsForDay($date){
            $items = Yii::$app->cache->getOrSet(RenderCache::cacheId('event-for-date-'.$date), function() use ($date) {
                $query = Event::find()->where('DATE(`publish_date`) = :date' , [':date' => $date])->orderBy('publish_date')->all();
                return $query;
            } ,3600*24, static::getCacheDependency());

            return $items;
        }


        public static function listForEditable(){

            $list = Event::find()->all();

            $res = [];
            foreach ($list as $item){
                $res[] =  ['value' =>  $item->id,  'text' => $item->title];
            }

            return $res;
        }
    }