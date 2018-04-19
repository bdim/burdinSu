<?
namespace app\widgets;

use app\models\Event;
use app\models\Files;
use yii\base\Widget;
use yii\data\ActiveDataProvider;
use yii\debug\models\timeline\DataProvider;
use yii\helpers\VarDumper;

class AttachWidget extends Widget
{
    /** @var Event $model */
    public $model;
    public $list;

    public function init()
    {
        $query = Files::find()->where("type_id in ("
            .implode(",", [ Files::TYPE_DOC, Files::TYPE_YANDEX_VIDEO, Files::TYPE_URL])
            .") AND `event_id` = :event_id",
            [':event_id' => $this->model->id]);

        $this->list = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);

    }

    public function run()
    {
        return $this->render('AttachWidget',['model' => $this->model, 'list' => $this->list]);
    }
}