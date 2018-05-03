<?php 
namespace yuanshuai\yscomponents\aliyun\oss;
use yii\base\Component;
/**
* 阿里云OSS接口对接文件
*/

require_once(YII2_PATH.'/../../aliyuncs/oss-sdk-php/autoload.php');
use OSS\OssClient;
use OSS\Core\OssException;
use OSS\Model\PrefixInfo;
class Aliyuncs extends Component
{
	public $config;
	public $isCName = false;
	public $securityToken = null;
	public $error = null;
	public $cdn = "";

    /**
     * @var OssClient $ossClient
     */
	private $ossClient;
	public function init(){
		try {
			$this->ossClient = new OssClient($this->getParam($this->config,'accessKeyId'),$this->getParam($this->config,'accessKeySecret'),$this->getParam($this->config,'endpoint'),$this->isCName,$this->securityToken);
		} catch (OssException $e) {
			$this->error = $e->getMessage();
		}
	}
	/**
	 * 获取当前配置
	 */
	public function config(){
		return $this->config;
	}
	/**
	 * 返回报错信息
	 */
	public function getError(){
		return $this->error;
	}
	/**
	 * 公共操作函数
	 */
	public function useApi($action,$options){
		try {
			$result = $this->$action($options);
			if (!$result) {
				$result = '操作成功';
			}
			return array('code'=>1,'result'=>$result);
		} catch (OssException $e) {
			$this->error = $e->getMessage();
			return array('code'=>0,'result'=>$this->error);
		}
	}
	/**
	 * 返回文件列表
	 */
	public function listObjects($options=array()){
		return $this->ossClient->listObjects($this->getParam($this->config,'bucket'),$options);
	}
	/**
	 * 删除文件
	 */
	public function deleteObject($options){
		return $this->ossClient->deleteObject($this->getParam($this->config,'bucket'),$options);
	}
	/**
	 * 批量删除文件
	 */
	public function deleteObjects($options){
		return $this->ossClient->deleteObjects($this->getParam($this->config,'bucket'),$options);
	}
	/**
	 * 格式化参数
	 */
	private function getParam($data,$flied,$default=''){
		return isset($data[$flied]) ? $data[$flied] : $default;
	}
	/**
	 * 获取目录
	 */
	public function getPrefixInfo($object,$prefix){
		$prefixInfo = new PrefixInfo($object->getKey());
		return $prefixInfo->getPrefix();
	}

    /**
     * 上传文件
     * @param $options
     * @return mixed
     */
	public function uploadFile($options){
		return $this->ossClient->uploadFile($this->getParam($this->config,'bucket'), $options['object'], $options['filepath']);
	}
}
?>