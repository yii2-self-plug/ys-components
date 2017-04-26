<?php
/**
 * Created by PhpStorm.
 * User: helong
 * Date: 2017/4/7
 * Time: 10:47
 */

namespace yuanshuai\yscomponents\amqp;

use yii\console\Controller;

/**
 * 工作者基类
 * Class Worker
 * @package yuanshuai\amqp
 */
class WorkerController extends Controller
{
    protected $amqp;
    protected $callBackName = "doJob";

    const START = "start";
    const SROP = "stop";
    const RESTART = "restart";
    const BIN_PATH = "/usr/local/php/bin/php";
    public function init()
    {
        $this->amqp = self::setAmqp();
    }

    public function actionRun($status = "start") {
        switch ($status) {
            case self::START:
                return $this->start();
                break;
            case self::STOP:
                return $this->stop();
                break;
            case self::RESTART:
                $this->stop();
                return $this->start();
                break;
            default:
                return false;
                break;
        }
    }

    public static function setAmqp(){
        return \Yii::$app->amqp;
    }

    /**
     * 重写此方法，返回具体job的名称。获取队列名称，指定获取队里的消息
     * @return string
     */
    public static function jobName(){
        return Job::jobName();
    }

    /**
     * 重写此方法，返回start路由
     * @return string
     */
    public static function routeName(){
        return "woker";
    }

    /**
     * @param $func
     */
    public function start(){
        //获取php目录
        $basePath = \Yii::$app->basePath;
        $cmd = self::BIN_PATH." {$basePath}/console/index ".static::routeName()."/start &";
        exec($cmd,$out);
        $outArray = explode(" ",$out[0]);
        $pid = $outArray[1];
        $portCmd = "netstat -antup | grep {$pid} | awk '{print $4}'";
        exec($portCmd,$portArray);
        $port = $portArray[0];

        return "{$port} -> 127.0.0.1:{$this->amqp->port}";
    }

    public function actionQueue(){
        return $this->amqp->getQuque(self::jobName());
    }

    /**
     * 可以用命令执行的开始函数
     */
    public function actionStart(){
        return $this->amqp->get([$this,$this->callBackName],["qname"=>self::jobName()]);
    }

    /**
     * 获取工作者状态
     * @param $consumerTag
     * @return mixed
     */
    public function status($connectName){
        return $this->amqp->status($connectName,["qname"=>self::jobName()]);
    }

    /**
     * 强制停止工作者
     * @param $consumerTag
     * @return mixed
     */
    public function stop($connectName){
        return $this->amqp->stop($connectName,["qname"=>self::jobName()]);
    }

    /**
     * 执行任务
     * @param $message 任务消息
     */
    public function doJob($message){
        echo $message;
    }
}