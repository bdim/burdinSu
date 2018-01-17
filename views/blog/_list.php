<?php
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use app\models\User;
use app\models\Blog;
use app\models\Files;
use app\models\Event;
use app\components\DateUtils;


    $files = Files::getItemsForDay($model['pub_date'], true);

    // записи из блога - только своим
    if (!Yii::$app->user->isGuest) {
        $blog  = Blog::getItemsForDay($model['pub_date']);
        $event = Event::getItemsForDay($model['pub_date']);

        /* если все пусто, то создаем блог */
        if (empty($blog) && empty($event) && !empty($files)) {
            $item = new Blog();
            $item->publish_date = $model['pub_date'];
            $item->save();

            $blog[] = $item;
        }
    }

    $tags = [];

    $out = [];
    if (!empty($blog)){
        foreach ($blog as $item) {

            $tags = $tags + $item->tagsIds;

            if (User::isUserEditor())
                $out['body'] .= $this->context->renderPartial('_body_editable',['data' => $item, 'controller' => 'blog']);
            else
                $out['body'] .= $this->context->renderPartial('_body',['data' => $item]);
        }
    }

    /* медиа */
    if (!empty($files)){
        $out['media'] = $this->context->renderPartial('_media',['data' => $files, 'show_date' => false, 'pub_date' => $model['pub_date']]);
    }


    /* события показываем отдельно */
    if (!empty($event))
        foreach ($event as $item) {

            $tags = $tags + $item->tagsIds;

            $out['event'][$item->id]['title'] = Yii::$app->formatter->asDate($item['date_start'],'php:d.m.Y l') .
                ( $item['date_start'] != $item['date_end'] ? " - ". Yii::$app->formatter->asDate($item['date_end'],'php:d.m.Y l') : '');

            if (User::isUserEditor())
                $out['event'][$item->id]['body'] .= $this->context->renderPartial('_event_editable',['data' => $item, 'controller' => 'event']);
            else
                $out['event'][$item->id]['body'] .= $this->context->renderPartial('_event',['data' => $item]);

            $files = Files::getItemsForEvent($item->id);
            if (!empty($files)){
                $out['event'][$item->id]['media'] = $this->context->renderPartial('_media',['data' => $files, 'show_date' => true, 'event_id' => $item->id]);
            }
        }

    // возраст
    $age = [];
    if (in_array(\app\models\Taxonomy::TAG_ARSENY, $tags)){
        $age[] = "Арсений - ".DateUtils::age("2012-05-12", $model['pub_date'], true);
    }
    if (in_array(\app\models\Taxonomy::TAG_YAROSLAV, $tags)){
        $age[] = "Ярослав - ".DateUtils::age("2016-08-18", $model['pub_date'], true);
    }
?>

<? if (!empty($out['body']) || !empty($out['media'])){ ?>
<div class="blog_item" id="blog-<?= $model['pub_date'] ?>">
    <div class="blog_item_title m20 pt20"><a class="link-" href="/blog#blog-<?= $model['pub_date'] ?>"><?= Yii::$app->formatter->asDate($model['pub_date'],'php:d.m.Y l') ?></a>
        <?= !empty($age) ? '<span class="blog_item_age">('.implode(", ", $age).')</span>' : '';?></div>
    <div class="blog_item_body m20 "><?= $out['body'];?></div>
    <div class="blog_item_media"><?= $out['media'] ?></div>
</div>
<hr>
<?}?>

<? if (!empty($out['event']))
    foreach ($out['event'] as $eventOut){?>
<div class="blog_item event_body_item"  id="event-<?= $model['pub_date'] ?>">
    <div class="blog_item_title m20 pt20"><a class="link-" href="/blog#event-<?= $model['pub_date'] ?>"><?= $eventOut['title'];?></a></div>
    <div class="blog_item_body m20 "><?= $eventOut['body'];?></div>
    <div class="blog_item_media"><?= $eventOut['media'] ?></div>
</div>
    <hr>
<?}?>
