<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 7/31/17
 * Time: 10:23 AM
 */

namespace yuanshuai\yscomponents\markdown;
use yii\web\AssetBundle;

/**
 * Class MarkdownAssets
 * @package yuanshuai\yscomponents\markdown
 */
class MarkdownAssets extends AssetBundle
{
    public $sourcePath = "@vendor/yuanshuai/ys-components/markdown/assets";

    public $js = [
        "editormd.js",
    ];

    public $css = [
        "css/editormd.css"
    ];
}