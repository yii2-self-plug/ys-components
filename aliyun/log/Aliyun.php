<?php
	namespace yuanshuai\yscomponents\aliyun\log;
	require_once 'Log_Autoload.php';
	use Aliyun_Log_Models_ListLogstoresRequest;
	use Aliyun_Log_Models_CreateLogstoreRequest;
	use Aliyun_Log_Models_UpdateLogstoreRequest;
	use Aliyun_Log_Models_DeleteLogstoreRequest;
	use Aliyun_Log_Models_GetLogsRequest;
	use Aliyun_Log_Models_PutLogsRequest;
	use Aliyun_Log_Models_LogItem;
	use Aliyun_Log_Client;
	class Aliyun extends \yii\base\Component{
		public $endpoint;
		public $accessKeyId;
		public $accessKey;
		public $project;
		public $logstore;

		private $client;
		public function init(){
			$this->client = new Aliyun_Log_Client($this->endpoint, $this->accessKeyId, $this->accessKey);
		}
		/**
		 * 获取当前project的所有logstroe
		 */
		public function listLogstores(){
			$model = new Aliyun_Log_Models_ListLogstoresRequest($this->project);
			return $this->client->listLogstores($model)->getLogstores();
		}
		/**
		 * 创建logstroe
		 * @param  [type] $logstore   logstroe名称
		 * @param  [type] $ttl        日志保存天数
		 * @param  [type] $shardCount 该logstore的shard数量
		 */
		public function createLogstore($ttl=null,$shardCount=null,$project=null,$logstore=null){
			if ($project) $this->project = $project;
			if ($logstore) $this->logstore = $logstore;
			$model = new Aliyun_Log_Models_CreateLogstoreRequest($this->project,$this->logstore,$ttl,$shardCount);
			return $this->client->createLogstore($model);
		}
		/**
		 * 修改logstroe
		 * @param  [type] $logstore   logstroe名称
		 * @param  [type] $ttl        日志保存天数
		 * @param  [type] $shardCount 该logstore的shard数量
		 */
		public function updateLogstore($ttl=null,$shardCount=null,$project=null,$logstore=null){
			if ($project) $this->project = $project;
			if ($logstore) $this->logstore = $logstore;
			$model = new Aliyun_Log_Models_UpdateLogstoreRequest($this->project,$this->logstore,$ttl,$shardCount);
			return $this->client->updateLogstore($model );
		}
		/**
		 * 删除logstroe
		 * @param  [type] $logstroe 要删除的logstroe名称
		 */
		public function deleteLogstore($project=null,$logstroe=null){
			if ($project) $this->project = $project;
			if ($logstroe) $this->logstore = $logstroe;
			$model = new Aliyun_Log_Models_DeleteLogstoreRequest($this->project,$this->logstore);
			return $this->client->deleteLogstore($model);
		}
		/**
		 * 添加日志
		 * @param  [type] $project  日志所在project
		 * @param  [type] $logstore 日志所在的logstore
		 * @param  [type] $topic    [description]
		 * @param  [type] $source   [description]
		 * @param  [type] $logs 日志：[[key=>value],[key]=>value]
		 * @param  [type] $shardKey [description]
		 * @return [type]           [description]
		 */
		public function putLogs($topic = null, $source = null, $logs = array(),$shardKey=null,$project = null, $logstore = null){
			$logitems = array();
			foreach ($logs as $key => $value) {
				$logItem = new Aliyun_Log_Models_LogItem();
				$logItem->setContents($value);
				$logitems[] = $logItem;
			}
			if ($project) $this->project = $project;
			if ($logstore) $this->logstore = $logstore;
			$model = new Aliyun_Log_Models_PutLogsRequest($this->project,$this->logstore,$topic,$source,$logitems,$shardKey);
			return $this->client->putLogs($model);
		}
		/**
		 * 查询日志
		 * @param  [type] $from     开始时间戳
		 * @param  [type] $to       结束时间戳
		 * @param  [type] $query    查询字段
		 * @param  [type] $topic    [description]
		 * @param  [type] $line     分页开始条数
		 * @param  [type] $offset   分页结束条数
		 * @param  [type] $reverse  [description]
		 * @param  [type] $project  [description]
		 * @param  [type] $logstore [description]
		 * @return [type]           [description]
		 */
		public function getLogs($from=null,$to=null,$query=null,$topic=null,$line=null,$offset=null,$reverse=false,$project=null,$logstore=null){
			if ($project) $this->project = $project;
			if ($logstore) $this->logstore = $logstore;

			$model = new Aliyun_Log_Models_GetLogsRequest($this->project,$this->logstore,$from,$to,$topic,$query,$line,$offset,$reverse);
			return $this->client->getLogs($model)->getLogs();
		}
	}
?>