<?php
/**
 * Created by PhpStorm.
 * User: helong
 * Date: 2017/3/28
 * Time: 17:21
 */

namespace yuanshuai\yscomponents\swoole\server;


use yii\helpers\Json;
use yuanshuai\yscomponents\swoole\ConstHelper;

class Server
{
    public $config = [];
    private $server;
    private $app;
    private $callBack = [
        'start',
        'workerStart',
        'managerStart',
        'receive',
        'task',
        'finish',
        'workerStop',
        'shutdown',
    ];

    public function __construct($config)
    {
        $this->app = \Yii::$app;
        $this->config = $config;
    }

    private function setting()
    {
        $this->server->set($this->config['setting']);
    }

    private function bulidCallBack()
    {
        foreach ($this->callBack as $v) {
            $m = 'on' . ucfirst($v);
            if (method_exists($this, $m)) {
                $this->server->on($v, [$this, $m]);
            }
        }
    }

    /**
     * 设置swoole进程名称
     * @param string $name swoole进程名称
     */
    private function setProcessName($name)
    {
        if (function_exists('cli_set_process_title')) {
            cli_set_process_title($name);
        } else {
            if (function_exists('swoole_set_process_name')) {
                swoole_set_process_name($name);
            } else {
                trigger_error(__METHOD__ . " failed.require cli_set_process_title or swoole_set_process_name.");
            }
        }
    }


    public function start()
    {
        $this->server = new \swoole_server($this->config['host'], $this->config['port']);
        $this->setting();
        $this->bulidCallBack();
        $this->server->start();
    }

    public function onStart($server)
    {
        //记录pid，方便停止服务和重启服务
        $pid = "{$this->server->master_pid}-{$this->server->manager_pid}";
        $this->setProcessName($server->setting['process_name'] . '-master');
        file_put_contents($this->config['pidfile'], $pid);
    }

    /**
     * [onManagerStart description]
     * @param  [type] $server [description]
     * @return [type]         [description]
     */
    public function onManagerStart($server)
    {
        $this->setProcessName($server->setting['process_name'] . '-manager');
    }

    /**
     * [onShutdown description]
     * @return [type] [description]
     */
    public function onClose()
    {
        $this->clearOpcache();
    }

    /**
     * [onWorkerStart description]
     * @param  [type] $server   [description]
     * @param  [type] $workerId [description]
     * @return [type]           [description]
     */
    public function onWorkerStart($server, $workerId)
    {
        if ($workerId >= $server->setting['worker_num']) {
            $this->setProcessName($server->setting['process_name'] . '-task');
        } else {
            $this->setProcessName($server->setting['process_name'] . '-event');
        }
    }

    /**
     * [onWorkerStop description]
     * @param  [type] $server   [description]
     * @param  [type] $workerId [description]
     * @return [type]           [description]
     */
    public function onWorkerStop($server, $workerId)
    {
        $this->clearOpcache();
    }

    /**
     * 任务处理
     * @param $server
     * @param $taskId
     * @param $fromId
     * @param $request
     * @return mixed
     */
    public function onTask($serv, $task_id, $from_id, $data)
    {
        try{
            $route = $data['route'];
            $params = isset($data['params']) ? $data['params'] : [];
            $result = $this->runAction($route,$params);
        }catch (\Exception $e){
            var_dump("Swoole Task Error:".$e->getMessage().";file:".$e->getFile().";on line:".$e->getLine());
        }
        return $data;
    }

    /**
     * 任务结束，返回数据
     * @param $server
     * @param $taskId
     * @param $data
     * @return mixed
     */
    public function onFinish($server, $taskId, $data)
    {
        //判断任务完成后的操作
        if (isset($data['finish'])) {
            $taskData = [
                'route'=>$data['finish']['route'],
                'params'=>$data['finish']['params'],
            ];
            $this->server->task($taskData);
        }
        return $data;
    }

    /**
     * 接收请求，开始任务
     * @param $server
     * @param $fd
     * @param $from_id
     * @param $data
     * @return bool
     */
    public function onReceive($serv, $fd, $from_id, $data)
    {
        try {
            $data = Json::decode($data);
            //返回状态
            if ($data['type'] == ConstHelper::SEND_STATUS) {
                $serv->send($fd,var_export($this->server->stats(),true),$from_id);
                $serv->close($fd);
            }
            //添加任务
            if ($data['type'] == ConstHelper::SEND_TASK) {
                $this->server->task($data);
                $serv->send($fd, Json::encode(['code'=>1,'message'=>'任务投递成功']));
                $serv->close($fd);
            }
            $params = isset($data['params']) ? $data['params'] : [];
            $result = $this->runAction($data['route'],$params);
            if ($result){
                if (!is_string($result)){
                    $result = Json::decode(Json::encode($result));
                }
                $serv->send($fd, Json::encode(['code'=>1,'message'=>'数据获取成功','data'=>$result]));
                $serv->close($fd);
            }
        } catch (\Exception $e) {
            var_dump("Swoole Erroe:".$e->getMessage());
            $serv->send($fd, Json::encode(['code' => 0, 'message' => '数据获取失败', 'because' => $e->getMessage()]));
            $serv->close($fd);
        }
        $serv->send($fd, Json::encode(['code' => 0, 'message' => '数据获取失败']));
        $serv->close($fd);
    }

    private function runAction($route,$data){
        $routeMap = $this->app->createController($route);
        if ($routeMap) {
            list($controller,$action) = $routeMap;
            $params = [];
            foreach ($data as $param){
                $params[] = $param;
            }
            $result = $controller->runAction($action,$params);
            if ($result) {
                return $result;
            }
        }
        return false;
    }

    /**
     * 清理代码缓存
     */
    public function clearOpcache() {
        /**
         * clear opcode cache,only support apc/opcache/eaccelerator
         */
        if (function_exists('apc_clear_cache')) {
            apc_clear_cache();
        }
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        if (function_exists('eaccelerator_purge')) {
            @eaccelerator_purge();
        }
    }
}