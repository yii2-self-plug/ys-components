<?php
namespace yuanshuai\yscomponents\webuploader;
use yii\web\AssetBundle;
class WebuploaderAsset extends AssetBundle
{
    public $sourcePath = '@vendor/bower/webuploader_fex/dist';
    public $css = [
        'webuploader.css'
    ];
    public $js = [
    	'webuploader.min.js'
    ];
    public $swf = 'Uploader.swf';
}