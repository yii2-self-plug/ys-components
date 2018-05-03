<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 7/17/17
 * Time: 4:33 PM
 */

namespace yuanshuai\yscomponents\extension\actions;
use yuanshuai\yscomponents\extension\FileHelper;

class CkeditorUploadAction extends BaseAction
{
    public function run()
    {
        FileHelper::$module = 'ckeditor';
        FileHelper::$filePath = "/upload/{:module}/{:Y}-{:M}-{:D}/{:name}.{:ext}";
        $result = FileHelper::upload('upload');
        $callback = $this->request->get("CKEditorFuncNum");
        if (!$result) {
            return $this->controller->error("上传失败",Response::FORMAT_JSON);
        }
        return "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction({$callback},'{$result["fileurl"]}','');</script>";
    }
}