<?php
namespace yuanshuai\yscomponents\icheck;
use yii\web\AssetBundle;
/**
 * Class IcheckAssets
 * @package yuanshuai\yscomponents\icheck
 */
class IcheckAssets extends AssetBundle {
    public $sourcePath = "@vendor/bower/admin-lte/plugins/iCheck/";
    public $js = [
        'icheck.js'
    ];
    public $css = [
        "all.css"
    ];
}
?>