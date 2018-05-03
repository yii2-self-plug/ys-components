<?php
\yuanshuai\yscomponents\modules\filemanager\assets\FileManagerAssets::register($this);
?>
<div id="filemanager" class="filemanager"></div>
<?php
$url = \yii\helpers\Url::to(["run"]);
$js = <<<JS
$("#filemanager").filemanager({
    url:'{$url}',
    languaje: "ZH",
    upload_max: 5,
    views:'thumbs',
    insertButton:true,
    token:'jashd4a5sd4sa'
});
JS;
/**
 * @var \yii\web\View $this
 */
$this->registerJs($js,\yii\web\View::POS_END);
?>