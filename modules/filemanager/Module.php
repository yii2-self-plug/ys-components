<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/15/17
 * Time: 3:13 PM
 */

namespace yuanshuai\yscomponents\modules\filemanager;

use yii\base\Module as BaseModule;

class Module extends BaseModule
{
    /**
     * @var string $path
     */
    public $path;
    /**
     * @var string $urlPath
     */
    public $urlPath;
    public function init()
    {
        if (!$this->path) {
            $this->path = \Yii::getAlias("@storage/web/upload");
        }
        if (!$this->urlPath) {
            $this->urlPath = \Yii::getAlias("@storage/web");
        }
        parent::init(); // TODO: Change the autogenerated stub
    }

    public function getPath()
    {
        return $this->path;
    }
}