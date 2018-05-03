<?php
	namespace yuanshuai\yscomponents\webuploader;
	use yii\helpers\Json;
	use yii\helpers\Html;
	/**
	* 图片上传
	*/
	class Webuploader extends \yii\widgets\InputWidget
	{
		public $server;
		public $cropperServer;
		public $name;
		public $options;
		public $cropperOption;
		public $value;
		public $imageUrl;
		public $boxid = 'packerImage';
		public $viewPath = "@vendor/yuanshuai/ys-components/webuploader/views";
		public function init(){
			parent::init();
			if (!$this->server) {
				$this->server = \Yii::$app->urlManager->createUrl('upload/webupload');
			}
			if (!$this->cropperServer) {
				$this->cropperServer = \Yii::$app->urlManager->createUrl('upload/cropper');
			}
			$cropperOption = array(
				'width'=>200,
				'height'=>200
			);
            $accept =Json::encode([
                'title'=>'Image',
                'extensions'=>'gif,jpg,jpeg,bmp,png',
                'mimeTypes'=>'image/gif,image/jpeg,image/png,image/jpg,image/bmp'
            ]);
			$options = array(
				'auto'=>'false',
				'multiple'=>'true',
				'accept'=>$accept,
			);
			if (!$this->options) {
				$this->options = $options;
			}else{
				$this->options = array_merge($options,$this->options);
			}
			if (!$this->cropperOption) {
				$this->cropperOption = $cropperOption;
			}else{
				$this->cropperOption = array_merge($cropperOption,$this->cropperOption);
			}
			if (!$this->value) {
				$this->value = '';
			}
			if ($this->hasModel()) {
			    if (!$this->name){
                    $this->name = Html::getInputName($this->model,$this->attribute);
                }
				$this->value = Html::getAttributeValue($this->model, $this->attribute);
			}
            if (!$this->imageUrl) {
                $this->imageUrl = $this->value;
            }
            if ($this->imageUrl instanceof \Closure) {
			    $this->imageUrl = ($this->imageUrl)();
            }
		}

		public function run(){
			$assetArray = array();
			$view = $this->getView();
			$asset = YsWebuploaderAsset::register($view);
			foreach ($asset->depends as $key => $value) {
				$assetArray[$key]=$value::register($view);
			}
			$assetArray['ysWebuploader'] = $asset;
			return $this->render($this->viewPath.'/upload',array(
				'key'=>$this->id,
				'view'=>$view,
				'asset'=>$assetArray,
				'server'=>$this->server,
				'name'=>$this->name,
				'value'=>$this->value,
				'imageUrl'=>$this->imageUrl,
				'boxid'=>$this->boxid,
				'options'=>$this->options,
				'cropperServer'=>$this->cropperServer,
				'cropperOptioin'=>Json::encode($this->cropperOption),
			));
		}
	}
?>