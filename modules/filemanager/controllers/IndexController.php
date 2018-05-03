<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/15/17
 * Time: 3:28 PM
 */

namespace yuanshuai\yscomponents\modules\filemanager\controllers;
use yii\web\Response;
use yuanshuai\yscomponents\modules\filemanager\helpers\FileHelper;

/**
 * Class IndexController
 * @package yuanshuai\yscomponents\modules\filemanager\controllers
 */
class IndexController extends BaseController
{
    const GET_FOLDER = "getfolder";//获取文件列表
    const UPLOAD_FILE = "uploadfile";//上传文件
    const NEW_FOLDER = "newfolder";//创建文件夹
    const RENAME_FILE = "renamefile";//重命名文件
    const MOVE_FILE = "movefile";//移动文件
    const DELETE_FILE = "deletefile";//删除文件
    const DOWNLOAD_FILE = "download";//下载文件

    public function actionIndex()
    {
        return $this->render("index");
    }

    public function actionRun()
    {
        $action = $this->get("action","getfolder");
        switch ($action) {
            case self::GET_FOLDER:
                $this->getFolfer();
                break;
            case self::UPLOAD_FILE:
                $this->uploadFile();
                break;
            case self::NEW_FOLDER:
                $this->newFolder();
                break;
            case self::RENAME_FILE:
                $this->renameFile();
                break;
            case self::MOVE_FILE:
                $this->moveFile();
                break;
            case self::DELETE_FILE:
                $this->deleteFile();
                break;
            case self::DOWNLOAD_FILE:
                return $this->downloadFile();
                break;
            default:
                $this->setAjaxData("status",0);
                break;
        }
        return $this->ajax();
    }

    /**
     * 获取文件列表
     * @inheritdoc
     */
    public function getFolfer()
    {
        $dir = $this->get("path","/");
        if(env("ENABLE_OSS",false)) {
            $list = \Yii::$app->oss->useApi("listObjects",[]);
            print_r($list);die;
        }else{
            $dir = $this->basePath.$dir;
            $list = FileHelper::getFiles($dir,$this->basePath,$this->urlPath);
        }
        $this->setData($list);
    }

    protected function setData($data,$msg = [
        "params"=>[],
        "query"=>""
    ])
    {
        $this->setAjaxData("status",1);
        $this->setAjaxData("data",$data);
        $this->setAjaxData("msg",$msg);
    }

    /**
     * 上传文件
     */
    public function uploadFile()
    {
        $dir = $this->get("path","/");
        $dir = $this->basePath.$dir;
        $files = $_FILES;
        $fileNames = [];
        if (is_array($files)) {
            foreach ($files['file']['name'] as $key=>$value) {
                $result = FileHelper::upload("file[{$key}]",$dir);
                $fileNames[] = $result["filename"];
            }
        }else{
            $result = FileHelper::upload("file",$dir);
            $fileNames[] = $result["filename"];
        }
        $this->setData($fileNames);
    }

    /**
     * 创建新文件夹
     */
    public function newFolder()
    {
        $dir = $this->get("path","/");
        $name = $this->get("name","");
        $basePath = $this->basePath.$dir;
        $path = "{$basePath}{$name}";
        FileHelper::createDirectory($path);
        $data = [
            "namefile"=>$name,
            "path"=>$dir,
        ];
        $msg = [
            "params"=>[
                "{$dir}{$name}"
            ],
            "query"=>"BE_NEW_FOLDER_CREATED %s",
        ];
        $this->setData($data,$msg);
    }

    /**
     * 重命名文件
     */
    public function renameFile()
    {
        $oldfile = $this->get("nameold","");
        $newname = $this->get("name");
        $path = $this->get("path","/");
        $filepath = $this->basePath.$path.$oldfile;
        if (!is_file($filepath) || !is_dir($filepath)) {
            $this->setAjaxData("status",0);
            return;
        }
        $newfilepath = $this->basePath.$path.$newname;
        if (is_file($filepath)) {
            $ext = FileHelper::getExtensionsByMimeType(FileHelper::getMimeType($filepath));
            $newfilepath = "{$newfilepath}.{$ext}";
        }

        $result = rename($filepath,$newfilepath);
        if (!$result) {
            $this->setAjaxData("status",0);
            return;
        }
        $data = [
            "namefile"=>$newname
        ];
        $msg = [
            "params"=>[],
            "query"=>"BE_RENAME_MODIFIED"
        ];
        $this->setData($data,$msg);
    }

    public function moveFile()
    {
        $oldname = $this->get("nameold","");
        $path = $this->get("path","/");
        $newname = $this->get("name","/");
        $oldpath = $this->basePath.$path.$oldname;
        if (!is_file($oldpath) && !is_dir($oldpath)) {
            $this->setAjaxData("status",0);
            return;
        }
        $newfilepath = $this->basePath.$newname.DIRECTORY_SEPARATOR.$oldname;
        if (is_file($oldpath)) {
            $ext = pathinfo($oldpath,PATHINFO_EXTENSION);
            $newfilepath = "{$newfilepath}.{$ext}";
        }

        $result = rename($oldpath,$newfilepath);
        if (!$result) {
            $this->setAjaxData("status",0);
            return;
        }
        $data = [
            "namefile"=>$newname.DIRECTORY_SEPARATOR.$oldname
        ];
        $msg = [
            "params"=>[],
            "query"=>"BE_MOVE_MOVED"
        ];
        $this->setData($data,$msg);
    }

    public function deleteFile()
    {
        $path = $this->get("path","/");
        $name = $this->get("name","");
        if (!$name) {
            $this->setAjaxData("status",0);
            return;
        }
        foreach ($name as $filename){
            $filepath = $this->basePath.$path.$filename;
            unlink($filepath);
        }
        $this->setData([],[[],"BE_DELETE_DELETED"]);
    }

    public function downloadFile()
    {
        $path = $this->get("path","/");
        $name = $this->get("name","");
        if (!$name) {
            $this->setAjaxData("status",0);
            return;
        }

        $file = $this->basePath.$path.$name;
        if (!is_file($file)) {
            $this->setAjaxData("status",0);
            return;
        }
        return $this->response->sendFile($file)->send();
    }
}