<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 6/30/17
 * Time: 2:34 PM
 */

namespace yuanshuai\yscomponents\extension\actions;
use yii\web\Response;
use yuanshuai\yscomponents\extension\FileHelper;

/**
 * Class UploadAction
 * @package yuanshuai\yscomponents\extension\actions
 */
class UploadAction extends BaseAction
{
    protected $fileField = "file";
    public $fileType = "image";
    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->controller->enableCsrfValidation = false;
        if ($this->request->post("field")) {
            $this->fileField = $this->request->post("field");
        }
        if ($this->request->post("type")) {
            $this->fileType = $this->request->post("type");
        }
    }

    public function run()
    {
        FileHelper::$module = $this->fileType;
        FileHelper::$filePath = "/upload/{:module}/{:Y}-{:M}-{:D}/{:name}.{:ext}";
        $result = FileHelper::upload($this->fileField);
        if (!$result) {
            return $this->controller->error("上传失败",Response::FORMAT_JSON);
        }

        return $this->controller->ajax($result);
    }
}