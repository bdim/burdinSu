<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use dosamigos\editable\Editable;

$this->title = 'События';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="filter-form">
    <form method="get" action="">
        <label for="year">Год: </label>
        <select name="year">
            <option value="">-</option>
            <?
            $selected = intval(Yii::$app->request->get('year'));
            $years = array_reverse(range(1993, date("Y")));
            foreach ($years as $year){?>
                <option value="<?=$year?>" <?= ($selected == $year) ? 'selected' : ''?> ><?=$year?></option>
            <?}?>
        </select>

        <span>&nbsp;&nbsp;&nbsp;&nbsp;</span>
        <input type="submit" value="фильтровать">
    </form>
</div>


<?
echo Html::a('Добавить',Url::to(['event/add']));
echo '<br><br>';

\yii\widgets\Pjax::begin(['id' => 'model-grid', 'enablePushState' => false]);
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'options' => [
        'class' => 'event-list',
    ],
    'columns' => [
        //['class' => 'yii\grid\SerialColumn'],
        'id',
         [
            'attribute' => 'date_start',
            'label' => 'Дата начала',
            'format' => 'raw',
            'value' => function($data){
                return Editable::widget( [
                    'model' => $data,
                    'attribute' => 'date_start',
                    'url' => 'event/update',
                    'type' => 'date',
                    /*'mode' => 'pop',*/
                    'clientOptions' => [
                        'showbuttons' => 'bottom',
                        'format' => 'yyyy-mm-dd',
                        'viewformat' => 'yyyy-mm-dd',
                        'datepicker' => [
                            'weekStart'=> 1
                        ]
                    ]
                ]);
            },
        ],[
            'attribute' => 'date_end',
            'label' => 'Дата конца',
            'format' => 'raw',
            'value' => function($data){
                return Editable::widget( [
                    'model' => $data,
                    'attribute' => 'date_end',
                    'url' => 'event/update',
                    'type' => 'date',
                    /*'mode' => 'pop',*/
                    'clientOptions' => [
                        'showbuttons' => 'bottom',
                        'format' => 'yyyy-mm-dd',
                        'viewformat' => 'yyyy-mm-dd',
                        'datepicker' => [
                            'weekStart'=> 1
                        ]
                    ]
                ]);
            },
        ],[
            'attribute' => 'publish_date',
            'label' => 'Дата публикации',
            'format' => 'raw',
            'value' => function($data){
                return Editable::widget( [
                    'model' => $data,
                    'attribute' => 'publish_date',
                    'url' => 'event/update',
                    'type' => 'datetime',

                    'clientOptions' => [
                        'showbuttons' => 'bottom',
                        'format' => 'yyyy-mm-dd hh:ii:ss',
                        'viewformat' => 'yyyy-mm-dd hh:ii',
                        'datetimepicker' => [
                            'weekStart'=> 1,
                        ]
                    ]
                ]);
            },
        ],[
            'attribute' => 'title',
            'options' => ['max-height' => '300px'],
            'label' => 'Заголовок',
            'format' => 'raw',
            'value' => function($data){
                return Editable::widget( [
                    'model' => $data,
                    'attribute' => 'title',
                    'url' => 'event/update',
                    'type' => 'text',

                    'clientOptions' => [
                        'showbuttons' => 'bottom',
                        'emptytext' => 'Заголовок',
                        'placeholder' => 'Заголовок ...'
                    ]
                ]);
            },
        ],[
            'label' => 'Тизер',
            'format' => 'raw',
            'value' => function($data){
                return Editable::widget( [
                    'model' => $data,
                    'attribute' => 'teaser',
                    'url' => 'event/update',
                    'type' => 'wysihtml5',
                    'clientOptions' => [
                        'showbuttons' => 'bottom',
                        'emptytext' => 'Текст',
                    ]
                ]);
            },
        ],[
            'label' => 'Текст',
            'format' => 'raw',
            'value' => function($data){
                return
                    '<a onclick="$(\'#spoiler'.$data->id.'\').toggle();" href="javascript://">Свернуть/Развернуть</a><div style="display:none;" id="spoiler'.$data->id.'">'.
                    Editable::widget( [
                    'model' => $data,
                    'attribute' => 'body',
                    'url' => 'event/update',
                    'type' => 'wysihtml5',
                    'clientOptions' => [
                        'showbuttons' => 'bottom',
                        'emptytext' => 'Текст',
                    ]
                ]) . '</div>';
            },
        ],[
            'label' => 'Доп.медиа',
            'format' => 'raw',
            'value' => function($data){
                return \app\widgets\AttachWidget::widget( [
                    'model' => $data,
                ]);
            },
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{delete}',
            'contentOptions' => ['class' => 'action-column'],
            'buttons' => [
                'delete' => function ($url, $model, $key) {
                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
                        'title' => Yii::t('yii', 'Delete'),
                        'data-pjax' => '#model-grid',
                    ]);
                },
            ],
        ],
        //['class' => 'yii\grid\ActionColumn','template' => ' {delete}'],
    ],
]);

\yii\widgets\Pjax::end();
?>
<style>
    .event-list {
        position: relative;
    }
</style>