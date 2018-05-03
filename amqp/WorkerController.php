<?php
/**
 * Created by PhpStorm.
 * User: helong
 * Date: 2017/4/7
 * Time: 10:47
 */

namespace yuanshuai\yscomponents\amqp;

use Imagine\Image\Point;
use yii\console\Controller;
use yuanshuai\yscomponents\amqp\components\AmqpComponent;

/**
 * 工作者基类
 * Class Worker
 * @package yuanshuai\amqp
 */
class WorkerController extends Controller
{
    /**
     * @var AmqpComponent $amqp
     */
    protected $amqp;
    protected $callBackName = "doJob";
    protected $pidfile;
    protected $jobClass = "yuanshuai\yscomponents\amqp\Job";
    /**
     * @var Job $job
     */
    protected $job;

    const START = "start";
    const STOP = "stop";
    const RESTART = "restart";
    const STATUS = "status";
    const STOPALL = "stopall";
    const WORKERS = "workers";
    const BIN_PATH = "/usr/local/php/bin/php";
    public function init()
    {
        $this->amqp = static::setAmqp();
        $moduleId = \Yii::$app->id;
        $this->job = \Yii::createObject(["class"=>$this->jobClass]);
        $this->pidfile = \Yii::getAlias("@{$moduleId}/runtime/{$this->getQName()}.pid");
    }

    public function actionRun($status = "start",$pid = null) {
        switch ($status) {
            case self::START:
                return $this->start();
                break;
            case self::STOP:
                return $this->stop($pid);
                break;
            case self::RESTART:
                $this->stop($pid);
                return $this->start();
                break;
            case self::STOPALL:
                $this->stopall();
                break;
            case self::STATUS:
                return $this->status($pid);
                break;
            case self::WORKERS:
                return $this->workers();
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
     * @return bool|string
     */
    public function start(){
        //获取php目录
        $basePath = \Yii::$app->basePath;
        $cmd = self::BIN_PATH." {$basePath}/index.php {$this->id}/start >> /dev/null 2>&1 & echo $! > {$this->pidfile}";
        exec($cmd,$out);
        $pid = file_get_contents($this->pidfile);
        return $pid;
    }

    public function actionQueue(){
        return $this->amqp->getQuque(static::jobName());
    }

    /**
     * 队列名称
     * @return string
     */
    protected function getQName()
    {
        return $this->job->getQName();
    }

    /**
     * 转发器名称
     * @return string
     */
    protected function getEName()
    {
        return $this->job->getEName();
    }

    /**
     * 可以用命令执行的开始函数
     */
    public function actionStart(){
        return $this->amqp->get([$this,$this->callBackName],["qname"=>$this->getQName(),"ename"=>$this->getEName()]);
    }

    /**
     * 获取工作者状态
     * @param $pid
     * @return mixed
     */
    public function status($pid = 0){
        if ($pid == 0) {
            return false;
        }
        exec("ps ax | awk '{print $1}' | grep -e {$pid}",$out);
        if (empty($out)) {
            return false;
        }
        return true;
    }

    /**
     * 强制停止工作者
     * @param $pid
     * @return mixed
     */
    public function stop($pid=0){
        if (empty($pid)) {
            $pid = file_get_contents($this->pidfile);
        }
        if ($this->status($pid)) {
            exec("kill -9 {$pid}",$out);
        }
        return !$this->status($pid);
    }

    /**
     * 强制停止所有
     */
    public function stopall()
    {
        $cmd = "ps aux | grep \"{$this->id}/start\" | grep -v 'grep' | awk '{print $2}'";
        exec($cmd,$out);
        foreach ($out as $pid) {
            $this->stop($pid);
        }
        return true;
    }

    /**
     * get All Worker pids
     */
    public function workers()
    {
        $cmd = "ps aux | grep \"{$this->id}/start\" | grep -v 'grep' | awk '{print $2}'";
        exec($cmd,$out);
        $data = [];
        foreach ($out as $pid) {
            $data[] = $pid;
        }
        return $data;
    }

    /**
     * 执行任务
     * @param $message 任务消息
     */
    public function doJob($message){
        echo $message;
    }
}