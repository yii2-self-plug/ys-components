<?php
namespace yuanshuai\yscomponents\date;
use yii\web\AssetBundle;

/**
 * Class DateAsstes
 * @package yuanshuai\yscomponents\date
 */
class DateAsstes extends AssetBundle
{
    public $sourcePath = "@vendor/bower/admin-lte/plugins/datepicker/";

    public $js = [
        "bootstrap-datepicker.js",
        "locales/bootstrap-datepicker.zh-CN.js"
    ];

    public $css = [
        "datepicker3.css"
    ];
}