<div id="<?=$id?>" >
    <textarea id="my-editormd-markdown-doc" name="<?=$name?>" style="display:none;"><?=$value?></textarea>
</div>
<?php
/**
 * @var \yii\web\View $this
 */
$js = <<<JS
var editor = editormd("{$id}",$options);
JS;
$this->registerJs($js,\yii\web\View::POS_END);
?>