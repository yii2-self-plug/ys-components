<?php
/**
 * Created by PhpStorm.
 * User: helong
 * Date: 2017/4/6
 * Time: 16:29
 */

namespace yuanshuai\yscomponents\swoole\console;


use yii\console\Controller;
use yuanshuai\yscomponents\swoole\ConstHelper;

class WebSocketController extends Controller
{
    protected $server;
    public function init(){
        $this->setServer();
        $this->server->setFunction([$this,"setFunction"]);
    }

    /**设置回调函数
     * @param $server
     * @return mixed
     */
    protected function setFunction($server)
    {
        $server->setFunction("open",[$this,"open"])
            ->setFunction("message",[$this,"message"])
            ->setFunction("close",[$this,"close"])
            ->setFunction("request",[$this,"request"])
            ->setFunction("push",[$this,"push"]);
        return $server;
    }

    /**
     * 当链接上的时候的回调函数
     * @param \swoole_websocket_server $server
     * @param $request
     */
    protected function open(\swoole_websocket_server $server, $request){}

    /**
     * 当客户端发送消息时候的回调函数
     * @param \swoole_websocket_server $server
     * @param $frame
     */
    protected function message(\swoole_websocket_server $server, $frame){}

    /**
     * 当客户端断开链接时候的回调函数
     * @param $ser
     * @param $fd
     */
    protected function close($ser, $fd){}

    /**
     * 当客户端发送HTTP请求时候的回调函数
     * @param $request
     * @param $response
     */
    protected function request($request, $response){}

    /**
     * 推送消息
     * @param $fd 接收消息的客户端
     * @param $data
     * @param bool $binary_data
     * @param bool $finish
     */
    protected function push($fd, $data, $binary_data = false, $finish = true){}

    /**
     * 设置配置文件中的配置项，默认为websocket
     */
    protected function setServer(){
        $this->server = \Yii::$app->websocket;
    }

    /**
     * 服务端启动脚本
     * @param string $status
     */
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

    /**
     * 启动服务
     */
    protected function start(){
        $this->server->setFunction()->start();
    }

    /**
     * 停止服务
     */
    protected function stop(){
        $this->server->stop();
    }

    /**
     * 重启服务
     */
    protected function restart(){
        $this->server->restart();
    }

    public function help(){
        echo "php yii websocket/run ".ConstHelper::RUN_START." 启动服务。".PHP_EOL;
        echo "php yii websocket/run ".ConstHelper::RUN_STOP." 停止服务。".PHP_EOL;
        echo "php yii websocket/run ".ConstHelper::RUN_RESTART." 重启服务。".PHP_EOL;
        echo "php yii websocket/run ".ConstHelper::RUN_HELP." 查看帮助。".PHP_EOL;
    }
}
