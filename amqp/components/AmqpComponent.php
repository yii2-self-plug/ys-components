<?php 
	namespace yuanshuai\yscomponents\amqp\components;
	use PhpAmqpLib\Connection\AMQPStreamConnection;
    use PhpAmqpLib\Message\AMQPMessage;
    use yii\base\Component;
    use yii\helpers\ArrayHelper;
    use yii\httpclient\Client;

    /**
	* amqp组件
	*/
	class AmqpComponent extends Component
	{
	    //对外配置文件
	    public $config = null;

	    //rabbitmq服务器地址
	    public $host = '127.0.0.1';
	    //rabbitmq服务端口
	    public $port = '5672';
	    //api调用端口
        public $apiport = '15672';
	    //rabbitmq服务登录账号
        public $login = 'guest';
	    //rabbitmq服务登录密码
        public $password = 'guest';
	    //rabbitmq用户对应vhost
        public $vhost = '/';

	    //exchange名称
        public $ename = 'exchange';
	    //exchange类型
        public $etype = 'direct';
	    //exchange是否持久化
        public $edurable = false;
	    //queue名称
        public $qname = 'queue';
	    //queue是否持久化
        public $qdurable = false;
	    //公平分配任务时每个消费者每次消费的条数
        public $prefetch_count = 10;

	    //消费者标签
        public $consumer_tag = 'consumer';
	    //exchange和queue是否在close时自动删除
        public $auto_delete = false;

	    //消息是否持久化
        public $mdurable = AMQPMessage::DELIVERY_MODE_NON_PERSISTENT;

	    //rabbitmq连接
        public $connect;
	    //rabbitmq通道
        public $channel;

        /**
         * 初始化系统系统变量
         */
        public function init() {
            $this->consumer_tag = $this->consumer_tag.getmypid();
            foreach ($this->config as $key => $value){
                if (property_exists($this,$key)) {
                    $this->$key = $value;
                }
            }
            $this->connect = new AMQPStreamConnection($this->host,$this->port,$this->login,$this->password,$this->vhost);
        }

        /**
         * 手动设置参数
         * @param array $config 需要额外设置的参数
         * @return $this 返回当前实例，方便用链式代码继续执行
         */
        public function set($config = []){
            $this->config = ArrayHelper::merge($this->config,$config);
            foreach ($this->config as $key => $value){
                if (property_exists($this,$key)) {
                    $this->$key = $value;
                }
            }
            $this->channel = $this->connect->channel();
            $this->channel->basic_qos(null,$this->prefetch_count,null);
            $this->channel->exchange_declare($this->ename,$this->etype,false,$this->edurable,$this->auto_delete);
            $this->channel->queue_declare($this->qname,false,$this->qdurable,false,$this->auto_delete);
            $this->channel->queue_bind($this->qname,$this->ename);
            return $this;
        }

        /**
         * 发送消息
         * @param $message 消息主体
         * @param array $config 额外参数
         */
        public function send($message,$config = []) {
            $this->set($config);
            $amqpMessage = new AMQPMessage($message,["delivery_mode" => $this->mdurable]);
            $this->channel->basic_publish($amqpMessage,$this->ename);

            $this->connect->close();
            $this->channel->close();
        }

        /**
         * 消费消息
         * @param array $config 额外参数设置
         */
        public function get($func = null,$config = []){
            $callBack = [$this,"getCallback"];
            if ($func){
                if (is_string($func) && function_exists($func)){
                    $callBack = $func;
                }
                if (is_array($func) && (list($obj,$function) = $func) && method_exists($obj,$function)){
                    $callBack = $func;
                }
            }
            $this->set($config);
            $this->channel->basic_consume($this->qname, $this->consumer_tag, false, false, false, false,function($message) use($callBack){
                $msg = $message->body;
                call_user_func($callBack,$msg);
                $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
            });
            register_shutdown_function(function($channel,$connection){
                $channel->close();
                $connection->close();
            }, $this->channel, $this->connect);

            while (count($this->channel->callbacks)) {
                $this->channel->wait();
            }
        }

        /**
         * 返回指定消费者的状态，是否挂掉
         * @param $consumerTag 消费者标签
         * @param array $config 额外参数设置
         * @return bool
         */
        public function status($connectName){
            $url = "http://{$this->host}:{$this->apiport}/api/connections/".urlencode($connectName);
            $response = $this->curl($url);
            if ($response->getIsOk()){
                return $response->getData();
            }
            return false;
        }

        /**
         * 强制停止指定消费者，容易造成消息丢失
         * @param $consumerTag
         * @param array $config
         * @return bool
         */
        public function stop($connectName) {
            $url = "http://{$this->host}:{$this->apiport}/api/connections/".urlencode($connectName);
            $response = $this->curl($url,"delete");
            if ($response->getIsOk()){
                return true;
            }
            return false;
        }

        /**
         * 获取队列详情
         * @param $qname
         * @param string $vhost
         * @return array|bool|mixed
         */
        public function getQuque($qname,$vhost = "/"){
            $qname = urlencode("{$vhost}/{$qname}");
            $url = "http://{$this->host}:{$this->apiport}/api/queues/{$qname}";
            $response = $this->curl($url,"delete");
            if ($response->getIsOk()){
                return $response->getData();
            }
            return false;
        }

        /**
         * 请求接口
         * @param $url
         * @param string $method
         * @param bool $isLogin
         * @return \yii\httpclient\Response
         */
        public function curl($url,$method="get",$isLogin=true){
            $client = new Client();
            $client->setTransport(\yii\httpclient\CurlTransport::className());
            $request = $client->createRequest()->setMethod($method)->setUrl($url);
            if ($isLogin){
                $request->setOptions([CURLOPT_USERPWD=>"{$this->login}:{$this->password}"]);
            }
            $response = $request->send();
            return $response;
        }

        /**
         * 消费消息时候的回调函数
         * @param $message
         */
        public function getCallback($message){
            echo $message.";ack:yes".PHP_EOL;
            sleep(2);
        }
	}
?>