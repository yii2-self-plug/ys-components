<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 8/4/17
 * Time: 1:39 PM
 */

namespace yuanshuai\yscomponents\date;
use common\components\helper\HtmlHelper;
use yii\base\Exception;
use yii\bootstrap\InputWidget;
use yii\helpers\Json;
use yuanshuai\yscomponents\extension\helpers\ArrayHelper;
use yuanshuai\yscomponents\iview\forms\ActiveField;

/**
 * Class Date
 * @package yuanshuai\yscomponents\date
 */
class Date extends InputWidget
{
    public $format = "yy-mm-dd";
    public $between = false;
    public $dateOptions = [];

    public $start;
    public $end;

    public $startValue;
    public $endValue;
    public $label;
    public $labelOptions = [];
    public $inputOptions = [];

    public function init()
    {
        $this->labelOptions = ArrayHelper::merge([
            "class"=>"control-label",
            "style"=>"padding-left:0px;"
        ],$this->labelOptions);
        $this->inputOptions = ArrayHelper::merge([
            "class"=>"form-control"
        ],$this->inputOptions);
        $this->dateOptions = ArrayHelper::merge([
            "autoclose"=>"true",
            "todayHighlight"=>"true",
            "dateFormat"=>$this->format
        ],$this->dateOptions);
        if ($this->between){
            if ($this->hasModel()) {
                $this->start = HtmlHelper::getInputName($this->model,$this->start);
                $this->end = HtmlHelper::getInputName($this->model,$this->end);

                $this->startValue = HtmlHelper::getAttributeValue($this->model,$this->start);
                $this->endValue = HtmlHelper::getAttributeValue($this->model,$this->end);
            }
        }else{
            $this->name = HtmlHelper::getInputId($this->field->model,$this->field->attribute);
        }
    }

    public function run()
    {
        DateAsstes::register($this->getView());
        if ($this->between) {
            $this->getView()->registerJs($this->registerBetwenJs());
            $label = HtmlHelper::label($this->label,"",$this->labelOptions);
            $text = HtmlHelper::tag("span","至",["class"=>"col-md-2","style"=>"line-height: 30px;"]);
            $startHtml = HtmlHelper::textInput($this->start,$this->startValue,ArrayHelper::merge($this->inputOptions,["id"=>$this->start]));
            $endHtml = HtmlHelper::textInput($this->end,$this->endValue,ArrayHelper::merge($this->inputOptions,["id"=>$this->end]));
            $dateGroup = HtmlHelper::tag("div","{$startHtml}\n{$text}\n{$endHtml}",[
                "class"=>"input-daterange input-group col-md-8"
            ]);
            echo HtmlHelper::tag("div","{$label}\n{$dateGroup}",[
                "class"=>"form-group col-md-4",
                "style"=>"padding-left:0px;"
            ]);
        }else{
            $this->getView()->registerJs($this->registerJs());
            echo $this->field->textInput($this->inputOptions);
        }
    }

    public function registerBetwenJs()
    {
        $dateOptions = Json::encode($this->dateOptions);
        $js = <<<JS
            $("#{$this->start}").datepicker({$dateOptions}).on('changeDate',function(e){  
                var startTime = e.date;  
                $('#{$this->end}').datepicker('setStartDate',startTime);  
            });
            $("#{$this->end}").datepicker({$dateOptions}).on('changeDate',function(e){  
                var endTime = e.date;  
                $('#{$this->start}').datepicker('setEndDate',endTime);  
            });
JS;
        return $js;
    }

    public function registerJs()
    {
        $dateOptions = Json::encode($this->dateOptions);
        $js = <<<JS
            $("#{$this->name}").datepicker({$dateOptions});
JS;
        return $js;
    }
}