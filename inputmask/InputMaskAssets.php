<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 8/4/17
 * Time: 3:45 PM
 */

namespace yuanshuai\yscomponents\inputmask;
use yii\web\AssetBundle;

/**
 * Class InputMaskAssets
 * @package yuanshuai\yscomponents\inputmask
 */
class InputMaskAssets extends AssetBundle
{
    public $sourcePath = "@vendor/bower/admin-lte/plugins/iCheck/";

    public $js = [
        "jquery.inputmask.js",
        "jquery.inputmask.extensions.js"
    ];
}