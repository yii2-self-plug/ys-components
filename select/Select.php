<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 8/4/17
 * Time: 11:10 AM
 */

namespace yuanshuai\yscomponents\select;
use yii\bootstrap\ActiveField;
use yii\bootstrap\InputWidget;
use yii\web\View;
use yuanshuai\yscomponents\extension\helpers\ArrayHelper;

/**
 * Class Select
 * @package yuanshuai\yscomponents\select
 */
class Select extends InputWidget
{
    public $items;
    public function init()
    {
        $this->options = ArrayHelper::merge([
            "id"=>$this->id
        ],$this->options);
    }

    public function run()
    {
        SelectAssets::register($this->getView());
        $this->getView()->registerJs($this->registerJs(),View::POS_END);
        echo $this->field->dropDownList($this->items,$this->options);
    }

    public function registerJs()
    {
        $js = <<<JS
            $("#{$this->id}").select2();
JS;
return $js;
    }
}