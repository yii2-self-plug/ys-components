<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 7/17/17
 * Time: 11:23 AM
 */
namespace yuanshuai\yscomponents\extension\widget;
use common\components\helper\HtmlHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\jui\InputWidget;
use yii\widgets\ActiveField;

/**
 * Class SelectLink
 * @package yuanshuai\yscomponents\extension\widget
 */
class SelectLink extends InputWidget
{
    /**
     * @var string $name 字段名称
     */
    public $name;

    /**
     * @var array $dataMap 下拉列表的值
     */
    public $dataMap = [];

    /**
     * @var string $linkName 联动字段
     */
    public $linkName;

    private $linkAttr;

    /**
     * @var string $ajaxUrl
     */
    public $ajaxUrl;

    public function init()
    {
        if ($this->hasModel()) {
            $this->linkAttr = Html::getInputName($this->model,$this->linkName);
        }
    }

    public function run()
    {
        $js = $this->getJs();
        $this->getView()->registerJs($js);
        echo HtmlHelper::activeDropDownList($this->model,$this->attribute,$this->dataMap,ArrayHelper::merge(
            $this->options,
            [
                "id"=>"{$this->linkName}",
                "class"=>"form-control",
                "prompt"=>"--请选择--",
            ]
        ));
    }

    public function getJs()
    {
        $js = <<<JS
            $("form").on("change","#{$this->linkName}",function(){
                $.post("{$this->ajaxUrl}",{"pid":$(this).val()},function(data) {
                    $('select[name="{$this->linkAttr}"]').html(data);
                    $('select[name="{$this->linkAttr}"]').change();
                })
            })
JS;
        return $js;
    }
}