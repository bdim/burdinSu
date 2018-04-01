<?php
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\bootstrap\ActiveForm;
use dosamigos\editable\Editable;
use app\models\Taxonomy;
use app\components\StringUtils;

$form = ActiveForm::begin(['id' => 'form-body-'.$data->id, 'fieldConfig' => ['template' => "{input}"]]);

?>
    <div><span class="blog_item_one_taxonomy"
    ><?

        $tags = Taxonomy::getVocabularyTags(Taxonomy::VID_BLOG_TAG);
        $source = [];
        foreach  ($tags as $tag)
            $source[] = [
                'value' => $tag->tid.':'.Taxonomy::VID_BLOG_TAG,
                'text'  => StringUtils::mb_ucfirst($tag->name)
            ];
        $source[] = [
            'value' => '0'.':'.Taxonomy::VID_BLOG_TAG,
            'text'  => '-пусто-'
        ];

        echo $form->field($data, 'tag')->widget(Editable::className(), [
            'url' => $controller.'/update',
            'type' => 'checklist',
            'value' =>  implode(", ",$data->getTagNames(Taxonomy::VID_BLOG_TAG)),
            //'mode' => 'pop',
            'clientOptions' => [
                'label' => 'Теги',
                'emptytext' => 'Про кого?',
                'value' =>  \yii\helpers\Json::encode($data->tag),
                'source' =>  $source,
            ]
        ]);

        ?></span><span class="blog_item_one_taxonomy"
    ><?

        $tags = Taxonomy::getVocabularyTags(Taxonomy::VID_THEME);
        $source = [];
        foreach  ($tags as $tag)
            $source[] = [
                'value' => $tag->tid.':'.Taxonomy::VID_THEME,
                'text'  => StringUtils::mb_ucfirst($tag->name)
            ];
        $source[] = [
            'value' => '0'.':'.Taxonomy::VID_THEME,
            'text'  => '-пусто-'
        ];

        echo $form->field($data, 'tag')->widget(Editable::className(), [
            'url' => $controller.'/update',
            'type' => 'checklist',
            'value' =>  implode(", ",$data->getTagNames(Taxonomy::VID_THEME)),
            //'mode' => 'pop',
            'clientOptions' => [
                'label' => 'Тематика',
                'emptytext' => 'Тематика',
                'value' =>  \yii\helpers\Json::encode($data->tag),
                'source' =>  $source,
            ]
        ]);

        ?></span>

<div class="blog_item_one_body">
    <?
    if (Yii::$app->params['devicedetect']['isDesktop']){
        echo $form->field($data, 'teaser')->widget(Editable::className(), [
            'url' => $controller.'/update',
            'type' => 'wysihtml5',
            'clientOptions' => [
                'emptytext' => 'Текст',
            ]
        ]);
    } else {
        echo $form->field($data, 'teaser')->widget(Editable::className(), [
            'url' => $controller.'/update',
            'type' => 'textarea',
            'mode' => 'pop',
            'clientOptions' => [
                'emptytext' => 'Текст',
                'placeholder' => 'Текст'
            ]
        ]);
    }

    ?>

</div>

<?  ActiveForm::end();?>