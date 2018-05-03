<?php
namespace yuanshuai\yscomponents\inputmask;
use common\components\helper\HtmlHelper;
use yii\bootstrap\InputWidget;
use yii\web\View;
use yuanshuai\yscomponents\iview\forms\ActiveField;

/**
 * Class InputMask
 * @package yuanshuai\yscomponents\inputmask
 */
class InputMask extends InputWidget
{
    public $fomart;

    public function run()
    {
        if ($this->fomart) {
            $this->name = HtmlHelper::getInputName($this->model,$this->attribute);
            InputMaskAssets::register($this->getView());
            $this->getView()->registerJs($this->registerJs(),View::POS_END);
        }
        $field = new ActiveField();
        $field->model = $this->model;
        $field->attribute = $this->attribute;
        echo $field->textInput($this->options);
    }

    public function registerJs()
    {
        $js = <<<JS
            $("#{$this->name}").inputmask("{$this->fomart}", {"placeholder": "{$this->fomart}"});
JS;
        return $js;
    }
}