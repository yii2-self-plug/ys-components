<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17-5-16
 * Time: 上午9:28
 */

namespace yuanshuai\yscomponents\extension;

use yuanshuai\yscomponents\extension\helpers\CdnHelper;
use Imagine\Image\Box;
use Imagine\Image\Point;
use trntv\filekit\File;
use yii\base\ErrorException;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper as BaseFileHelper;
use yii\httpclient\Client;
use yii\imagine\Image;
use yii\web\UploadedFile;

class FileHelper extends BaseFileHelper
{
    const TYPE_DIR = "dir";
    const TYPE_FILE = "file";
    const TYPE_IMAGE = "image";
    const DOWNLOAD_IMAGES_TYPE = [
        "jpg","jpeg","gif","png","bmp"
    ];

    public static $basePath = "@storage/web";
    public static $filePath = "/upload/{:module}/{:Y}/{:M}/{:D}/{:name}.{:ext}";
    public static $cdnDomain = "@storageUrl";
    public static $module = "";

    /**
     * @param string $name
     * @param string $path
     * @param Model $model
     * @return array|bool
     */
    public static function upload($name,$path=null,$model = null)
    {
        if ($model !== null) {
            $file = UploadedFile::getInstance($model,$name);
        }else{
            $file = UploadedFile::getInstanceByName($name);
        }
        if (empty($file)) {
            return false;
        }
        if ($file->getHasError()) {
            return false;
        }
        \Yii::warning($file);
        if (is_null($path)) {
            $path = static::getPath($file->getExtension());
        }else{
            $fileName = time().rand (1000,9999).".".$file->getExtension();
            $path = $path.DIRECTORY_SEPARATOR.$fileName;
        }
        $dir = dirname($path);
        if (!is_dir($dir)) {
            static::createDirectory($dir);
        }

        $result = $file->saveAs($path);
        $filePath = strtr($path,[\Yii::getAlias(static::$basePath)=>""]);
        self::_uploadToOss($path);
        if (!$result) {
            return false;
        }
        if (env("TENCENT_COS_OPEN",false)) {
            $data = \Yii::$app->cos->upload($path,$filePath);
        }
        $data = [
            "filepath"=>$filePath,
            "fileurl"=>static::getCdnDomain($filePath),
            "filename"=>basename($path),
            "filesize"=>$file->size,
            "filetype"=>$file->type,
            "fileext"=>$file->getExtension()
        ];
        return $data;
    }

    /**
     * 通过base64上传
     * @param $base64Data
     * @param $fileName
     * @param $fileType
     * @param $path
     * @return boolean|array
     */
    public static function uploadByBase64($base64Data,$fileName,$fileType,$path = null)
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (is_null($path)) {
            $path = static::getPath($extension);
        }else{
            $fileName = time().rand (1000,9999).".".$extension;
            $path = $path.DIRECTORY_SEPARATOR.$fileName;
        }
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64Data, $baseData)){
            $base64Data = base64_decode(str_replace($baseData[1], '', $base64Data));
        }
        $dir = dirname($path);
        if (!is_dir($dir)) {
            self::createDirectory($dir);
        }
        $result = file_put_contents($path,$base64Data);
        if ($result) {
            $filePath = strtr($path, [\Yii::getAlias(static::$basePath) => ""]);
            $filesize = filesize($path);
            self::_uploadToOss($path);
            $data = [
                "filepath" => $filePath,
                "fileurl" => static::getCdnDomain($filePath),
                "filename" => basename($path),
                "filesize" => $filesize,
                "filetype" => $fileType,
                "fileext" => $extension
            ];
            return $data;
        }
        return false;
    }

    private static function _uploadToOss($path)
    {
        if (env("ENABLE_OSS",false)) {
            $options = [
                "object"=>trim(strtr($path,[\Yii::getAlias(static::$basePath)=>'']),"/"),
                "filepath"=>$path
            ];
            $data = \Yii::$app->oss->useApi("uploadFile",$options);
            if ($data["code"] && $data["code"] == 1 && !empty($cdn = \Yii::$app->oss->cdn)){
                //上传成功删除源文件
                unlink($path);
                static::$cdnDomain = $cdn;
            }
        }
    }

    /**
     * @param string $path
     * @return string
     */
    public static function getCdnDomain($path)
    {
        return CdnHelper::url($path);
    }

    /**
     * @param  $dir
     * @param null $type
     * @return array|bool
     */
    public static function getChilds($dir,$type = null)
    {
        if (!is_dir($dir)) {
            return false;
        }

        $childs = scandir($dir);
        $return = [];
        foreach ($childs as $child) {
            if ($child == "." || $child == "..") {
                continue;
            }
            if (is_null($type)) {
                $return[] = $child;
            }else if ($type == "dir") {
                if (is_dir("{$dir}/{$child}")) {
                    $return[] = $child;
                }
            }else if ($type == "file") {
                if (is_file("{$dir}/{$child}")) {
                    $return[] = $child;
                }
            }
        }

        return $return;
    }

    /**
     * @param string $ext
     * @return bool|string
     */
    public static function getPath($ext)
    {
        $path = static::$basePath.strtr(static::$filePath,[
            "{:module}"=>empty(static::$module) ? \Yii::$app->id : static::$module,
            "{:Y}"=>date("Y",time()),
            "{:M}"=>date("m",time()),
            "{:D}"=>date("d",time()),
            "{:name}"=>time().rand (1000,9999),
            "{:ext}"=>$ext,
        ]);
        $path = \Yii::getAlias($path);
        return $path;
    }

    /**
     * @param array $params
     * @return array|bool
     */
    public static function cropper($params)
    {
        $filePath = static::$basePath.$params["imgUrl"];
        $filePath = \Yii::getAlias($filePath);

        $oldFile = File::create($filePath);

        $cropperPath = static::getPath($oldFile->getExtension());
        $dir = dirname($cropperPath);
        if (!is_dir($dir)) {
            static::createDirectory($dir);
        }
        if (!ArrayHelper::getValue($params,"imgUrl",null)){
            return false;
        }

        $imageInfo = getimagesize($filePath);
        $imageFrame = Image::frame($filePath);
        $start = new Point(ArrayHelper::getValue($params,"x",0),ArrayHelper::getValue($params,"y",0));
        $box = new Box(ArrayHelper::getValue($params,"width",$imageInfo[0]),ArrayHelper::getValue($params,"height",$imageInfo[1]));
        $imageFrame->crop($start,$box);
        if ($rotate = ArrayHelper::getValue($params,"rotate",0)) {
            $imageFrame = $imageFrame->rotate($rotate);
        }
        $imageFrame->save($cropperPath);

        $file = File::create($cropperPath);
        return [
            "filepath"=>strtr($cropperPath,[\Yii::getAlias(static::$basePath)=>""]),
            "fileurl"=>static::getCdnDomain(strtr($cropperPath,[\Yii::getAlias(static::$basePath)=>""])),
            "filename"=>basename($cropperPath),
            "filesize"=>$file->getSize(),
            "filetype"=>$file->getMimeType(),
            "fileext"=>$file->getExtension()
        ];
    }

    /**
     * @param string $dir
     * @param string $basePath
     * @param bool $all
     * @return array
     */
    public static function getFiles($dir,$basePath,$urlPath,$all=false)
    {
        if (!is_dir($dir)) {
            throw new InvalidParamException("The dir argument must be a directory: $dir");
        }
        $dir = rtrim($dir, DIRECTORY_SEPARATOR);
        $list = [];
        $handle = opendir($dir);
        if ($handle === false) {
            throw new InvalidParamException("Unable to open directory: $dir");
        }
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..' || $file === '.gitignore') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (static::filterPath($path, [])) {
                $filetype = is_dir($path) ? "" : explode(".",$file);
                if (is_array($filetype)) {
                    $filetype = count($filetype) > 0 ? $filetype[count($filetype) - 1] : "";
                }
                $item = [
                    "filename"=>$file,
                    "filetype"=>$filetype,
                    "isdir"=>is_dir($path),
                    "lastmodified"=>filemtime($path),
                    "size"=>is_dir($path) ? 0 : filesize($path),
                    "urlfolder"=>is_dir($path) ? "/".strtr($path,[$basePath=>""])."/" : "",
                    "preview"=>is_dir($path) ? "" : CdnHelper::url(strtr($path,[$urlPath=>""])),
                    "previewfull"=>CdnHelper::url(strtr($path,[$urlPath=>""]))
                ];
                $list[] = $item;
                if (is_dir($path) && $all){
                    $list = array_merge($list, static::getFiles($path,$basePath,$urlPath, $all));
                }
            }
        }
        closedir($handle);

        return $list;
    }

    /**
     * @param string $file
     * @return boolean bool
     */
    public static function isImage($file)
    {
        if (!is_file($file)) {
            return false;
        }
        $mimeType = self::getMimeType($file);
        $mimeTypeArray = explode("/",$mimeType);
        if ($mimeTypeArray[0] === "image") {
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     * @param string $file
     * @return boolean $bool
     */
    public static function removeFile($file)
    {
        if (is_file($file)) {
            try {
                unlink($file);
                return true;
            } catch (ErrorException $e) {
                \Yii::warning("remove file error".$e->getMessage());
            }
        }
        return true;
    }

    /**
     * @param $url
     * @param string $module
     * @return array|bool
     */
    public static function DownloadFile($url,$module = "common")
    {
        $pathInfo = pathinfo($url);
        if (isset($pathInfo["extension"]) && in_array($pathInfo["extension"],self::DOWNLOAD_IMAGES_TYPE)) {
            try{
                self::$module = $module;
                $fileName = self::getPath($pathInfo["extension"]);
                $fileContent = file_get_contents($url);
                $fileDir = dirname($fileName);
                if (!is_dir($fileDir)) {
                    self::createDirectory($fileDir);
                }
                $result = file_put_contents($fileName,$fileContent);
                if ($result) {
                    self::_uploadToOss($fileName);
                    $filePath = strtr($fileName,[\Yii::getAlias(static::$basePath)=>""]);
                    $data = [
                        "filePath"=>$filePath,
                        "fileUrl"=>static::getCdnDomain($filePath),
                    ];
                    return $data;
                }
            }catch (\Exception $e) {
                \Yii::warning($e->getMessage(),__METHOD__);
                return false;
            }
        }
        return false;
    }
}