<?php
/**
 * Created by PhpStorm.
 * User: helong
 * Date: 2017/3/29
 * Time: 16:52
 */

namespace yuanshuai\yscomponents\swoole\client;

use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yuanshuai\yscomponents\swoole\ConstHelper;

class Client
{
    private $client;
    private $data = [];
    //对应服务端的controller
    private $group;
    private $dataSouce;
    private $config = [
        'host'=>ConstHelper::CONFIG_HOST,
        'port'=>ConstHelper::CONFIG_PORT,
        'client_timeout'=>ConstHelper::CONFIG_CLIENT_TIMEOUT,
    ];
    public function __construct($group = "default",$dataSouce = "mysql")
    {
        $this->group = $group;
        $this->dataSouce = $dataSouce;
        $this->setConfig();
        $this->client = new \swoole_client(SWOOLE_SOCK_TCP);
        $this->connect();
    }

    /**
     * 配置
     */
    protected function setConfig(){
        if (isset(\Yii::$app->params["serverClient"][$this->group])){
            $this->config = ArrayHelper::merge($this->config,\Yii::$app->params["serverClient"][$this->group]);
        }else{
            $this->config = ArrayHelper::merge($this->config,\Yii::$app->params["serverClient"]["default"]);
        }
    }

    /**
     * 魔术函数，将未知函数调用指向服务端
     * @param $name
     * @param $arguments
     * @return $this
     */
    public function __call($name, $arguments)
    {
        $route = $this->getRoute($this->group,$name);
        $arguments[0]["dataSouce"] = $this->dataSouce;
        $this->data = ArrayHelper::merge(['route'=>$route],["params"=>$arguments]);
        return $this->tcp();
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
        $controller = Inflector::camel2id($className);
        $action = Inflector::camel2id($action);
        return "{$controller}/{$action}";
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
        $result = $this->client->recv();
        $result = Json::decode($result);
        if ($result["code"] === 1) {
            return $result["data"];
        }
        return false;
    }
}