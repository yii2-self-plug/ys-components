<?php 
namespace yuanshuai\yscomponents\yunpian;
/**
* 云片发短信接口
*/
use yii\helpers\Json;
use \Yunpian\Sdk\YunpianClient;
use yii\base\Component;
class Yunpian extends Component
{
	public $config;

	public function getText($text){
		return str_replace('#code#', $text, $this->config['temp']);
	}

	/**
	 * 发送单条短信
	 * @param  string $mobile 电话号码
	 * @param  string $text   发送内容
     * @return object|boolean
	 */
	public function sendOneMessage($mobile,$text){
        $client = YunpianClient::create($this->config['appkey']);
		$data[YunpianClient::MOBILE] = $mobile;
		$data[YunpianClient::TEXT] = $this->getText($text);
		$result = $client->sms()->single_send($data);
		if ($result->isSucc()) {
		    return $result->data();
        }
        \Yii::error("短信发送失败:".Json::encode($result));
        return false;
	}
}
?>