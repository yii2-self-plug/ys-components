<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 6/30/17
 * Time: 2:36 PM
 */

namespace yuanshuai\yscomponents\extension\actions;
use yii\base\Action;
use yii\web\Request;
use yii\web\Response;
use yii\web\Session;
use yuanshuai\yscomponents\extension\WebController;

/**
 * Class BaseAction
 * @package yuanshuai\yscomponents\extension\actions
 */
class BaseAction extends Action
{
    /**
     * @var WebController
     */
    public $controller;
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var Session
     */
    protected $session;

    public function init()
    {
        $this->request = \Yii::$app->request;
        $this->response = \Yii::$app->response;
        $this->session = \Yii::$app->session;
        parent::init();
    }
}