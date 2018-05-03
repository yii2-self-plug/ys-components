<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/15/17
 * Time: 3:36 PM
 */

namespace yuanshuai\yscomponents\modules\filemanager\assets;
use yii\web\AssetBundle;
use yii\web\View;

/**
 * Class FileManagerAssets
 * @package yuanshuai\yscomponents\modules\filemanager\assets
 */
class FileManagerAssets extends AssetBundle
{
    public $sourcePath = "@vendor/yuanshuai/ys-components/modules/filemanager/static";

    public $js = [
        "js/filemanager-ui-without.js"
    ];

    public $css = [
        "css/filemanager-ui.css"
    ];

    public $jsOptions = [
        "position" => View::POS_END
    ];
}