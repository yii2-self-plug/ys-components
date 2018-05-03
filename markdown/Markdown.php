<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 7/31/17
 * Time: 10:30 AM
 */

namespace yuanshuai\yscomponents\markdown;
use yii\bootstrap\InputWidget;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yuanshuai\yscomponents\extension\helpers\ArrayHelper;

/**
 * Class Markdown
 * @package yuanshuai\yscomponents\markdown
 */
class Markdown extends InputWidget
{
    protected $plugins = [
        "font-color"=>"yuanshuai\\yscomponents\\markdown\\FontColorAssets",
    ];
    public $usePlugins = [];
    public $jsOptions;
    public function init()
    {
        if ($this->hasModel()) {
            $this->name = Html::getInputName($this->model,$this->attribute);
            $this->value = Html::getAttributeValue($this->model,$this->attribute);
        }
    }

    public function run()
    {
        $asset = MarkdownAssets::register($this->getView());
        foreach ($this->usePlugins as $key) {
            ($this->plugins[$key])::register($this->getView());
        }
        $libPath = "{$asset->baseUrl}/lib/";
        if ($this->jsOptions && $this->jsOptions instanceof \Closure) {
            $this->options = ($this->jsOptions)($libPath);
        }else{
            $this->options = ArrayHelper::merge([
                "width"=>"100%",
                "height"=>"640",
                "path"=>$libPath,
            ],$this->options);
        }
        return $this->render("markdown",[
            "options"=>is_array($this->options) ? Json::encode($this->options) : $this->options,
            "name"=>$this->name,
            "value"=>$this->value,
            "id"=>$this->getId()
        ]);
    }
}