<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 8/4/17
 * Time: 9:49 AM
 */

namespace yuanshuai\yscomponents\extension\widget;
use yii\bootstrap\ActiveField;
use yii\bootstrap\InputWidget;
/**
 * Class Select
 * @package yuanshuai\yscomponents\extension\widget
 */
class Select extends InputWidget
{
    public $items;
    public $form;

    public function run()
    {
        $field = new ActiveField(["form"=>$this->form]);
        $field->model = $this->model;
        $field->attribute = $this->attribute;
        return $field->dropDownList($this->items,$this->options);
    }
}