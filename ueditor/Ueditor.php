<?php
	namespace yuanshuai\yscomponents\ueditor;
	use yii\helpers\Json;
	use yii\helpers\Html;
	class Ueditor extends \yii\widgets\InputWidget{
		public $name;
		public $server;
		public $toolbars;
		public $value = '';
		public function init(){
			if (!$this->server) {
				$this->server = \Yii::$app->urlManager->createUrl('ueditor/index');
			}
			if (!$this->toolbars) {
				$this->toolbars = array(
					'fullscreen', 'source', '|', 'undo', 'redo', '|',
		            'bold', 'italic', 'underline', 'fontborder', 'strikethrough', 'superscript', 'subscript', 'removeformat', 'formatmatch', 'autotypeset', 'blockquote', 'pasteplain', '|', 'forecolor', 'backcolor', 'insertorderedlist', 'insertunorderedlist', 'selectall', 'cleardoc', '|',
		            'rowspacingtop', 'rowspacingbottom', 'lineheight', '|',
		            'customstyle', 'paragraph', 'fontfamily', 'fontsize', '|',
		            'directionalityltr', 'directionalityrtl', 'indent', '|',
		            'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|', 'touppercase', 'tolowercase', '|',
		            'link', 'unlink', 'anchor', '|', 'imagenone', 'imageleft', 'imageright', 'imagecenter', '|',
		            'simpleupload', 'insertimage', 'emotion', 'scrawl', 'insertvideo', 'music', 'attachment', 'map', 'gmap', 'insertframe', 'insertcode', 'webapp', 'pagebreak', 'template', 'background', '|',
		            'horizontal', 'date', 'time', 'spechars', 'snapscreen', 'wordimage', '|',
		            'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', 'charts', '|',
		            'print', 'preview', 'searchreplace', 'drafts', 'help'
				);
			}
			if ($this->hasModel()) {
				$this->name = Html::getInputName($this->model,$this->attribute);
				$this->value = Html::getAttributeValue($this->model, $this->attribute);
			}
			parent::init();
		}

		public function run(){
			$view = $this->getView();
			UeditorAsset::register($this->getView());
			return $this->render('ueditor',array(
				'view'=>$view,
				'server'=>$this->server,
				'toolbars'=>Json::encode(array($this->toolbars)),
				'name'=>$this->name,
				'value'=>$this->value,
			));
		}
	}
?>