<?php
/**
 * Created by PhpStorm.
 * User: helong
 * Date: 2017/4/6
 * Time: 16:24
 */

namespace yuanshuai\yscomponents;


use yii\base\Component;
use yuanshuai\yscomponents\server\WebSocket;
use Yii;

class WebSocketComponent extends Component
{
    use SwooleTrait;
    public $config = [
        'host'=>ConstHelper::WEB_SOCKET_HOST,
        'port'=>ConstHelper::WEB_SOCKET_PORT,
        'pidfile'=>ConstHelper::WEB_SOCKET_PID_FILE,
        'setting'=>[
            'daemonize'=>true
        ]
    ];

    private $server;
    public function init(){
        if (!isset($this->config['setting']['log_file'])){
            $this->config['setting']['log_file'] = Yii::getAlias("@app/runtime/logs/websocket.log");
        }
        $this->check();
        $this->server = new WebSocket($this->config);
    }

    public function setFunction($func = null){
        $callBack = [$this,"serverCallBack"];
        if (!is_null($func)) {
            $callBack = $func;
        }
        $this->server = call_user_func($callBack,$this->server);
        return $this;
    }

    public function serverCallBack($server){
        $server->setFunction("open",[$server,"onOpen"])
            ->setFunction("message",[$server,"onMessage"])
            ->setFunction("close",[$server,"onClose"])
            ->setFunction("request",[$server,"onRequest"]);
        return $server;
    }

    public function start(){
        $this->show("服务开始启动。。。。");
        $pidfile = $this->config['pidfile'];
        $host = $this->config['host'];
        $port = $this->config['port'];
        if (file_exists($pidfile)){
            $pid = explode('-',file_get_contents($pidfile));
            exec("ps ax | awk '{print $1}' | grep -e {$pid[0]}",$out);
            if (!empty($out)){
                $this->show("服务已启动，进程ID为:{$pid[0]}",true);
            } else {
                unlink($pidfile);
            }
        }

        //检测端口占用
        $bind = $this->checkPort($port);
        foreach ($bind as $item){
            if ($item['ip'] == '*' || $item['ip'] == 'localhost' ||  $item['ip'] == $host) {
                $this->show("{$port}端口已经被占用",true);
            }
        }

        $this->show("服务启动成功");
        $this->server->run();
    }
}