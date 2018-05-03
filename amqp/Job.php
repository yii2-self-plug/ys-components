<?php
/**
 * Created by PhpStorm.
 * User: helong
 * Date: 2017/4/7
 * Time: 9:52
 */

namespace yuanshuai\yscomponents\amqp;
use Imagine\Image\Point;
use yii\base\Component;
use yii\base\Exception;
use yii\helpers\Json;
use yii\helpers\StringHelper;

/**
 * 任务基类
 * Class Job
 * @package yuanshuai\amqp
 */
class Job extends Component
{
    private $amqp;
    private $_attributes = [];
    public function init()
    {
        $this->amqp = $this->setAmqp();
    }

    /**
     * 设置rabbitmq
     * @return mixed
     */
    public function setAmqp(){
        return \Yii::$app->amqp;
    }

    /**
     * 返回转发器名称，当为广播消息时，注意不要和QNAME相同
     * @return string
     */
    public function getEName(){
        return $this->getQName();
    }

    /**
     * 返回队列名称
     * @return string
     */
    public function getQName()
    {
        return StringHelper::basename(get_called_class());
    }

    /**
     * 发送队列消息
     */
    public function send()
    {
        $message = Json::encode($this->getAttributes());
        $this->amqp->send($message,["qname"=>$this->getQName(),"ename"=>$this->getQName()]);
    }

    public function __get($name)
    {
        return $this->getAttribute($name);
    }

    public function __set($name, $value)
    {
        $this->_attributes[$name] = $value;
    }

    public function getAttribute($name,$value="")
    {
        return $this->hasAttribute($name) ? $this->_attributes[$name] : $value;
    }

    public function hasAttribute($name)
    {
        return isset($this->_attributes[$name]);
    }

    public function getAttributes()
    {
        return $this->_attributes;
    }

    public function setAttributes($arr = [])
    {
        if (!is_array($arr)) {
            throw new Exception("attributes must be array");
        }
        $this->_attributes = $arr;
        foreach ($arr as $key => $value) {
            if ($this->hasProperty($key)) {
                $this->$key = $value;
            }
        }
    }
}