<div>
    <ul>

        <?
        echo \yii\grid\GridView::widget([
            'dataProvider' => $list,
            'options' => [
                'class' => 'file-list',
            ],
            'columns' => [
                //['class' => 'yii\grid\SerialColumn'],
                'id',
                [
                    'label' => 'файл',
                    'format' => 'raw',
                    'value' => function($data){
                        return end(explode("/", $data->path));
                    },
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{delete}',
                    'contentOptions' => ['class' => 'action-column'],
                    'buttons' => [
                        'delete' => function ($url, $model, $key) {
                            return \yii\helpers\Html::a('<span class="glyphicon glyphicon-trash"></span>', '/event/delete-file/' . $model->id, [
                                'title' => Yii::t('yii', 'Delete'),
                                'data-pjax' => '#model-grid',
                            ]);
                        },
                    ],
                ],

            ],
        ]);
        ?>
        <?php yii\widgets\Pjax::begin(['id' => 'new_note' . $model->id]) ?>
        <form enctype="multipart/form-data" action="#" id="attach-form-<?= $model->id ?>">
            <input type="file" id="attach-file-<?= $model->id ?>" name="Files[z_file]">
            <button type="button" id="submit-<?= $model->id ?>">OK</button>
        </form>
        <?php \yii\widgets\Pjax::end();


        ?>

    </ul>
</div>

<script>

   $("#submit-<?= $model->id?>").on('click',function(){
       var z_file;

       var fd = new FormData();
       var e = document.getElementById("attach-file-<?= $model->id ?>");
       fd.append( "Files[z_file]", $(e)[0].files[0]);
       fd.append( "Files[event_id]", "<?= $model->id ?>");

       $.ajax({
           url: "<?= \yii\helpers\Url::to(['site/attachfile']) ?>",
           type: "POST",
           cache: false,
           data: fd,
           datatype: "json",
           processData: false,
           contentType: false,
           success: function (data) {
               $.pjax.reload({container:"#model-grid"});
           },
           error: function () {
           }
       });
   });




</script>