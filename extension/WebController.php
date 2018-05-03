<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17-5-15
 * Time: 下午3:36
 */

namespace yuanshuai\yscomponents\extension;


use yii\helpers\Url;
use yii\web\Controller;
use yii\helpers\ArrayHelper;
use Yii;
use yii\web\Request;
use yii\web\Response;
use yii\web\Session;
use yuanshuai\yscomponents\extension\actions\CkeditorUploadAction;
use yuanshuai\yscomponents\extension\actions\CropperAction;
use yuanshuai\yscomponents\extension\actions\MarkdownUploadAction;
use yuanshuai\yscomponents\extension\actions\UploadAction;
use yuanshuai\yscomponents\extension\actions\UploadFileAction;

/**
 * 重写webController
 * Class WebController
 * @package yuanshuai\yscomponents\extension
 */
class WebController extends Controller
{
    public function actions()
    {
        return [
            'upload' => [
                'class' => UploadAction::className(),
                'fileType'=>$this->id
            ],
            'cropper' => [
                'class' => CropperAction::className()
            ],
            'ckeditor-upload'=>[
                'class'=>CkeditorUploadAction::className()
            ],
            'markdown-upload'=>[
                'class'=>MarkdownUploadAction::className(),
                'module' => $this->id
            ],
            'upload-file'=>[
                'class'=>UploadFileAction::className(),
                'module' => $this->id
            ]
        ];
    }

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

    protected $ajaxData = [
        "code"=>200,
        "message"=>"成功",
        "data"=>[]
    ];

    public function beforeAction($action)
    {
        $_POST = XssHelper::filter($_POST);
        $_GET = XssHelper::filter($_GET);
        return parent::beforeAction($action); // TODO: Change the autogenerated stub
    }

    public function init(){
        $this->request = Yii::$app->getRequest();
        $this->response = Yii::$app->getResponse();
        $this->session = Yii::$app->getSession();
        parent::init();
    }

    /**
     * 从请求中获取值,包含POST和GET请求域以及通过php://input获取的数据
     * @param $key
     * @param string $value
     * @return string|array
     */
    protected function get($key = null, $value = '') {
        $params = ArrayHelper::merge($this->request->get(), $this->request->post());
        if (is_null($key)) {
            return XssHelper::filter($params);
        }
        if (isset($params[$key])) {
            return XssHelper::filter($params[$key]);
        }
        foreach($this->request->getBodyParams() as $k=>$val) {
            if (!isset($params[$k])) {
                $params[$k] = $val;
            }
        }
        if (isset($params[$key])) {
            return XssHelper::filter($params[$key]);
        }
        if ($this->request->getCookies()->has($key)) {
            return XssHelper::filter($this->request->getCookies()->getValue($key));
        }
        return $value;
    }

    public function error($message = null,$format = null,$jump = null,$code = 500){
        if (is_array($message)) {
            $this->session->addFlash("error",Yii::t(Yii::$app->id,$message[0],$message[1]));
        }else{
            $this->session->addFlash("error",Yii::t(Yii::$app->id,$message));
        }
        if (is_null($jump)) {
            $jump = Url::to(["index"]);
        }
        if ($format == Response::FORMAT_JSON) {
            $this->response->format = $format;
            $this->ajaxData["code"] = $code;
            $this->ajaxData["message"] = $this->session->getFlash("error")[0];
            $this->ajaxData["jump"] = $jump;
            return $this->ajaxData;
        }

        return $this->redirect($jump);
    }

    public function success($message = null,$format = null,$jump = null){
        $this->session->addFlash("success",Yii::t(Yii::$app->id,$message));
        if (is_null($jump)) {
            $jump = Url::to(["index"]);
        }
        if ($format == Response::FORMAT_JSON) {
            $this->response->format = $format;
            $this->ajaxData["message"] = $this->session->getFlash("success")[0];
            $this->ajaxData["jump"] = $jump;
            return $this->ajaxData;
        }

        return $this->redirect($jump);
    }

    public function ajax($data=[]){
        if (isset($this->ajaxData["data"])) {
            $this->ajaxData["data"] = ArrayHelper::merge($this->ajaxData["data"],$data);
        }else{
            $this->ajaxData["data"] = $data;
        }
        return $this->asJson($this->ajaxData);
    }

    public function setAjaxData($key, $value)
    {
        $this->ajaxData[$key] = $value;
    }

    protected function isPost(){
        return $this->request->getIsPost();
    }

    protected function isAjax(){
        return $this->request->getIsAjax();
    }

    /**
     * 结束响应
     * @throws \yii\base\ExitException
     */
    public function end() {
        try {
            Yii::$app->end();
        }catch (\Exception $e) {
            exit(0);
        }
    }
}