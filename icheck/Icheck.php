<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 8/4/17
 * Time: 10:43 AM
 */

namespace yuanshuai\yscomponents\icheck;
use yii\bootstrap\ActiveField;
use yii\bootstrap\InputWidget;
use yii\helpers\Html;
use yii\web\View;
use yuanshuai\yscomponents\extension\helpers\ArrayHelper;

/**
 * Class Icheck
 * @package yuanshuai\yscomponents\icheck
 */
class Icheck extends InputWidget
{
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_RADIO = 'radio';
    public $type;
    public $items;
    public $icheckClass;
    public $rollback = [];

    public function init()
    {

        if (!$this->type) {
            $this->type = static::TYPE_CHECKBOX;
        }
        if (!$this->icheckClass) {
            $this->icheckClass = "icheck-{$this->id}";
        }
        $this->options = ArrayHelper::merge([
            "class"=>$this->icheckClass,
        ],$this->options);
    }

    public function run()
    {
        $js = $this->registerJs();
        IcheckAssets::register($this->getView());
        $this->getView()->registerJs($js,View::POS_END);
        $checkHtml = "";
        switch ($this->type) {
            case static::TYPE_CHECKBOX:
                $checkHtml = $this->field->checkboxList($this->items,$this->options);
                break;
            case static::TYPE_RADIO:
                $checkHtml = $this->field->radioList($this->items,$this->options);
                break;
        }
        echo $checkHtml;
    }

    protected function registerJs()
    {
        $rollback = [];
        foreach ($this->rollback as $method => $value) {
            $rollback[] = '
                $(".'.$this->icheckClass.'").on("'.$method.'",'.$value.');
            ';
        }
        $rollback = implode("\n",$rollback);
        $js = <<<JS
            $(".{$this->icheckClass}").iCheck({
                checkboxClass: 'icheckbox_flat-blue',
                radioClass: 'iradio_flat-blue'
            });
            {$rollback}
JS;
        return $js;
    }
}