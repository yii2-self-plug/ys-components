<?php
namespace yuanshuai\yscomponents\select;
use yii\web\AssetBundle;
/**
 * Class SelectAssets
 * @package yuanshuai\yscomponents\select
 */
class SelectAssets extends AssetBundle
{
    public $sourcePath = "@vendor/bower/admin-lte/plugins/select2/";
    public $js = [
        "select2.full.min.js"
    ];

    public $css = [
        "select2.css"
    ];
}