<?php
namespace yuanshuai\yscomponents\webuploader;
use yii\web\AssetBundle;
class YsWebuploaderAsset extends AssetBundle
{
    public $sourcePath = '@vendor/yuanshuai/ys-webuploader/dist';
    public $css = [
        'style.css'
    ];
    public $js = [
    	'uploader.js',
    	'cropper.js'
    ];
    public $depends = [
    	'webuploader'=>'yuanshuai\webuploader\WebuploaderAsset',
    	'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'yuanshuai\webuploader\CropperAsset'
    ];
}