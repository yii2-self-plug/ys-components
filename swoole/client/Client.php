<?php
/**
 * Created by PhpStorm.
 * User: helong
 * Date: 2017/3/29
 * Time: 16:52
 */

namespace yuanshuai\yscomponents\client;

use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yuanshuai\swoole\ConstHelper;

class Client
{
    private $client;
    private $data = [];
    private $config = [
        'host'=>ConstHelper::CONFIG_HOST,
        'port'=>ConstHelper::CONFIG_PORT,
        'pidfile'=>ConstHelper::CONFIG_PID_FILE,
        'client_timeout'=>ConstHelper::CONFIG_CLIENT_TIMEOUT,
    ];
    public function __construct()
    {
        $this->setConfig();
        $this->client = new \swoole_client(SWOOLE_SOCK_TCP);
        $this->connect();
    }

    /**
     * 配置
     */
    protected function setConfig(){
        if (isset(\Yii::$app->params[$this->className()])){
            $this->config = \Yii::$app->params[$this->className()];
        }
    }

    /**
     * 服务端路由,也是params的配置项
     * @return string
     */
    protected function className(){
        return "Client";
    }

    /**
     * 魔术函数，将未知函数调用指向服务端
     * @param $name
     * @param $arguments
     * @return $this
     */
    public function __call($name, $arguments)
    {
        $className = $this->className();
        $route = $this->getRoute($className,$name);
        $this->data = ArrayHelper::merge(['route'=>$route],["params"=>$arguments]);
        return $this;
    }

    /**
     * TCP链接
     * @return string
     */
    public function tcp(){
        $this->data = ArrayHelper::merge(['type'=>ConstHelper::SEND_TCP],$this->data);
        return $this->send();
    }

    /**
     * 获取当前服务端状态
     * @return string
     */
    public function status(){
        $this->data = ArrayHelper::merge(['type'=>ConstHelper::SEND_STATUS],$this->data);
        return $this->send();
    }

    /**
     * 投递任务
     * @param array $finish
     * ["finish"=>["route"=>"hello/file","params"=>["message"=>"测试"]]]
     * @return string
     */
    public function task($finish = []){
        $this->data = ArrayHelper::merge(['type'=>ConstHelper::SEND_TASK],$this->data,$finish);
        return $this->send();
    }

    /**
     * 返回路由
     * @param $className
     * @param $action
     * @return string
     */
    private function getRoute($className,$action){
        $controller = $this->foramtRoute($className);
        $action = $this->foramtRoute($action);
        return "{$controller}/{$action}";
    }

    /**
     * 格式化路由
     * @param $name
     * @return mixed
     */
    private function foramtRoute($name){
        $name = lcfirst($name);
        $str = preg_replace_callback('/([A-Z]{1})/',function($matches){
            return '-'.strtolower($matches[0]);
        },$name);
        return $str;
    }

    /*
     * 连接服务端
     */
    private function connect(){
        if (!$this->client->connect($this->config['host'], $this->config['port'], $this->config['client_timeout'])){
            exit("Error: connect server failed. code[{$this->client->errCode}]\n");
        }
    }

    /**
     * 向服务端发送数据
     * @return string
     */
    private function send(){
        $this->client->send(Json::encode($this->data));
        return $this->client->recv();
    }
}