<?php
	namespace yuanshuai\yscomponents\ueditor;
	use yii\web\AssetBundle;
	class UeditorAsset extends AssetBundle{
	    public $sourcePath = '@vendor/npm/ueditor/example/public/ueditor';
	    public $js = [
	    	'ueditor.config.js',
	      	'ueditor.all.js',
	      	'lang/zh-cn/zh-cn.js'
	    ];
	}
?>