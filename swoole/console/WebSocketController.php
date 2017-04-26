<?php
/**
 * Created by PhpStorm.
 * User: helong
 * Date: 2017/4/6
 * Time: 16:29
 */

namespace yuanshuai\yscomponents\console;


use yii\console\Controller;
use yuanshuai\swoole\ConstHelper;

class WebSocketController extends Controller
{
    protected $server;
    public function init(){
        $this->setServer();
    }

    protected function setServer(){
        $this->server = \Yii::$app->websocket;
    }

    public function actionRun($status = 'start'){
        switch ($status){
            case ConstHelper::RUN_START :
                $this->start();
                break;
            case ConstHelper::RUN_STOP:
                $this->stop();
                break;
            case ConstHelper::RUN_RESTART:
                $this->restart();
                break;
            case ConstHelper::RUN_HELP:
                $this->help();
                break;
            default:
                echo  '指令不存在';
                break;
        }
        exit();
    }

    protected function start(){
        $this->server->setFunction()->start();
    }

    protected function stop(){
        $this->server->stop();
    }

    protected function restart(){
        $this->server->restart();
    }

    public function help(){
        echo "php yii websocket/run ".ConstHelper::RUN_START." 启动服务。".PHP_EOL;
        echo "php yii websocket/run ".ConstHelper::RUN_STOP." 停止服务。".PHP_EOL;
        echo "php yii websocket/run ".ConstHelper::RUN_RESTART." 重启服务。".PHP_EOL;
        echo "php yii websocket/run ".ConstHelper::RUN_HELP." 查看帮助。".PHP_EOL;
//        echo "php yii swoole/run ".ConstHelper::RUN_STATUS." 查看状态。".PHP_EOL;
    }
}