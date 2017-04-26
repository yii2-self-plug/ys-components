<?php
/**
 * Created by PhpStorm.
 * User: helong
 * Date: 2017/4/6
 * Time: 15:38
 */

namespace yuanshuai\yscomponents\server;


use yii\helpers\ArrayHelper;

class WebSocket
{
    public $config = [];
    private $websocket;
    private $app;

    public function __construct($config = []){
        $this->config = ArrayHelper::merge($this->config,$config);
        $this->app = \Yii::$app;
        $this->websocket = new \swoole_websocket_server($this->config['host'],$this->config['port']);
        $this->websocket->set($this->config['setting']);
    }

    public function setFunction($name,$func){
        $this->websocket->on($name,$func);
        return $this;
    }

    public function run(){
        $this->websocket->on("start",[$this,"onStart"]);
        $this->websocket->start();
    }

    public function onStart($server){
        //记录pid，方便停止服务和重启服务
        $pid = "{$this->websocket->master_pid}-{$this->websocket->manager_pid}";
        file_put_contents($this->config['pidfile'], $pid);
    }

    public function onOpen(\swoole_websocket_server $server, $request){
        echo "server: handshake success with fd{$request->fd}\n";
    }

    public function onMessage(\swoole_websocket_server $server, $frame){
        echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
        foreach ($server->connections as $fd) {
            $server->push($fd, "this is messge:{$frame->data}");
        }
    }

    public function onClose($ser, $fd){
        echo "client {$fd} closed\n";
    }

    public function onRequest($request, $response){

    }
}