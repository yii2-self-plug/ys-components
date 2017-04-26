<?php
	namespace yuanshuai\yscomponents\geetest;
	use yii\base\Component;
	/**
	* 极致验证yii2整合
	*/
	class Geetest extends Component
	{
		public $config;
		public $user = 'mybns';

		private $geetest;
		public function init(){
			$this->geetest = new GeetestLib($this->config['GEE_TEST_ID'], $this->config['GEE_TEST_KEY']);
		}

		public function captcha(){
	    	return $this->geetest->get_response_str();
		}

		public function gtserver(){
			return $this->geetest->pre_process($this->user);
		}

		public function success_validate($challenge,$validate,$seccode){
			return $this->geetest->success_validate($challenge, $validate, $seccode, $this->user);
		}

		public function fail_validate($challenge,$validate,$seccode){
			return $this->geetest->fail_validate($challenge,$validate,$seccode);
		}
	}
?>