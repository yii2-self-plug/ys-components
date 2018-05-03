<?php
/**
 * Created by PhpStorm.
 * User: helong
 * Date: 2017/4/6
 * Time: 16:24
 */

namespace yuanshuai\yscomponents\swoole;


use yii\base\Component;
use Yii;
use yii\helpers\ArrayHelper;
use yuanshuai\yscomponents\swoole\server\WebSocket;

class WebSocketComponent extends Component
{
    public $defaultConfig = [
        'host'=>ConstHelper::WEB_SOCKET_HOST,
        'port'=>ConstHelper::WEB_SOCKET_PORT,
        'pidfile'=>ConstHelper::WEB_SOCKET_PID_FILE,
        'setting'=>[
            'daemonize'=>true
        ]
    ];

    public $config = [];

    /**
     * @var WebSocket $server
     */
    private $server;
    public function init(){
        $this->config = ArrayHelper::merge($this->defaultConfig,$this->config);
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

    /**
     * @param WebSocket $server
     * @return mixed
     */
    public function serverCallBack($server){
        $server->setFunction("open",[$server,"onOpen"])
            ->setFunction("message",[$server,"onMessage"])
            ->setFunction("close",[$server,"onClose"])
            ->setFunction("request",[$server,"onRequest"])
            ->setFunction("push",[$server,"push"]);
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

    public function stop(){
        $this->show("服务停止开始。。。。");
        $pidfile = $this->config['pidfile'];
        if (!file_exists($pidfile)){
            $this->show("[error]:PID文件不存在，请确认服务是否启动,use php yii swoole/run start",true);
        }
        $pid = explode("-", file_get_contents($pidfile));
        if ($pid[0]) {
            $cmd = "kill {$pid[0]}";
            exec($cmd);
            do {
                $out = [];
                $c = "ps ax | awk '{ print $1 }' | grep -e \"^{$pid[0]}$\"";
                exec($c, $out);
                if (empty($out)) {
                    break;
                }else{
                    exec("kill -9 {$pid[0]}");
                }
            } while (true);
        }
        //删除文件
        if (file_exists($pidfile)) {
            unlink($pidfile);
        }
        $this->show("服务已停止");
    }

    public function status(){
        $client = new \swoole_client(SWOOLE_SOCK_TCP);
        if (!$client->connect($this->config['host'], $this->config['port'], $this->config['client_timeout'])){
            exit("Error: connect server failed. code[{$client->errCode}]\n");
        }
        $client->send(Json::encode(['type'=>ConstHelper::SEND_STATUS]));
        echo $client->recv();
    }

    public function restart(){
        $this->stop();
        $this->start();
    }

    private function checkPort($port){
        $res = [];
        $cmd = "/usr/sbin/lsof -i :{$port}|awk '$1 != \"COMMAND\"  {print $1, $2, $9}'";
        exec($cmd, $out);
        if ($out) {
            foreach ($out as $v) {
                $a = explode(' ', $v);
                list($ip, $p) = explode(':', $a[2]);
                $res[$a[1]] = [
                    'cmd'  => $a[0],
                    'ip'   => $ip,
                    'port' => $p,
                ];
            }
        }
        return $res;
    }

    private function show($msg,$exit=false){
        if ($exit){
            exit($msg.PHP_EOL);
        }else{
            echo $msg.PHP_EOL;
        }
    }

    private function check(){
        if (!function_exists("exec")){
            $this->show("error:exec函数没有开启,修改php.ini文件，use php --ini".PHP_EOL,true);
        }
        exec("whereis lsof", $out);
        if (strpos($out[0], "/usr/sbin/lsof") === false ) {
            $this->show('error:找不到lsof命令,请确保lsof在/usr/sbin下' . PHP_EOL,true);
        }
    }
}