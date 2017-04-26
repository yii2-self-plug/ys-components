<?php
/**
 * Created by PhpStorm.
 * User: helong
 * Date: 2017/4/7
 * Time: 9:52
 */

namespace yuanshuai\yscomponents\amqp;
use yii\helpers\Json;

/**
 * 任务基类
 * Class Job
 * @package yuanshuai\amqp
 */
class Job
{
    private $amqp;
    public function __construct()
    {
        $this->amqp = self::setAmqp();
    }

    /**
     * 设置rabbitmq
     * @return mixed
     */
    public static function setAmqp(){
        return \Yii::$app->amqp;
    }

    /**
     * 返回类名，将类名作为队列名称
     * @return string
     */
    public static function jobName(){
        return get_called_class();
    }

    /**
     * 发送队列消息
     * @param $message
     */
    public function send($message){
        $this->amqp->send($message,["qname"=>self::jobName()]);
    }
}