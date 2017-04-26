<?php
namespace yuanshuai\yscomponents\webuploader;
use yii\web\AssetBundle;
class CropperAsset extends AssetBundle
{
    public $sourcePath = '@vendor/bower/cropper/dist';
    public $css = [
        'cropper.min.css'
    ];
    public $js = [
    	'cropper.min.js'
    ];
}