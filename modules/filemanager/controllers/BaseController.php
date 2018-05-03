<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 9/15/17
 * Time: 3:24 PM
 */

namespace yuanshuai\yscomponents\modules\filemanager\controllers;
use yuanshuai\yscomponents\extension\WebController;
use yuanshuai\yscomponents\modules\filemanager\Module;

/**
 * Class BaseController
 * @package yuanshuai\yscomponents\modules\filemanager\controllers
 */
class BaseController extends WebController
{
    /**
     * @var Module $module
     */
    public $module;

    public $basePath;
    public $urlPath;

    public function init()
    {
        $this->enableCsrfValidation = false;
        $this->basePath = $this->module->path;
        $this->urlPath = $this->module->urlPath;
        parent::init(); // TODO: Change the autogenerated stub
    }
}