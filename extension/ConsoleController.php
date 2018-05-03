<?php
/**
 * Created by PhpStorm.
 * User: helong
 * Date: 2017/5/21
 * Time: 上午9:18
 */

namespace yuanshuai\yscomponents\extension;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

/**
 * Class ConsoleController
 * @package yuanshuai\yscomponents\extension
 */
class ConsoleController extends Controller
{
    protected $params = [];
    public function runAction($id,$params=[])
    {
        if (!empty($params)){
            $this->params = $params[0];
        }
        return parent::runAction($id,$params);
    }

    public function get($name = null,$default = "")
    {
        return is_null($name) ? $this->params : ArrayHelper::getValue($this->params,$name,$default);
    }
}