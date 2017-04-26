<?php 
namespace yuanshuai\yscomponents\yunpian;
/**
* 云片发短信接口
*/
require_once 'YunpianAutoload.php';
use SmsOperator;
use UserOperator;
use TplOperator;
class Yunpian extends \yii\base\Component
{
	public $config;

	public function getText($text){
		return str_replace('#code#', $text, $this->config['temp']);
	}

	/**
	 * 发送单条短信
	 * @param  string $mobile 电话号码
	 * @param  string $text   发送内容
	 */
	public function sendOneMessage($mobile,$text){
		$smsOperator = new SmsOperator($this->config['appkey']);
		$data['mobile'] = $mobile;
		$data['text'] = $this->getText($text);
		return $smsOperator->batch_send($data);
	}
	/**
	 * 返回配置信息
	 * @return array 配置数组
	 */
	public function config(){
		return $this->config;
	}
	/**
	 * 返回用户基本信息
	 */
	public function getInfo(){
		$userOperator = new UserOperator($this->config['appkey']);
		return $userOperator->get();
	}
	/**
	 * 获取模板列表
	 */
	public function getTpl($tplId=null){
		$tplOperator = new TplOperator($this->config['appkey']);
		$data = array();
		if ($tplId) {
			$data['tpl_id'] = $tplId;
		}
		return $tplOperator->get($data);
	}
	/**
	 * 保存模板
	 */
	public function saveTpl($tplInfo){
		$tplOperator = new TplOperator($this->config['appkey']);
		if (array_key_exists('tpl_id',$tplInfo)) {
			return $tplOperator->upd($tplInfo);
		}
		return $tplOperator->add($tplInfo);
	}
	public function delTpl($tplId){
		$tplOperator = new TplOperator($this->config['appkey']);
		return $tplOperator->del(array('tpl_id'=>$tplId));
	}
}
?>