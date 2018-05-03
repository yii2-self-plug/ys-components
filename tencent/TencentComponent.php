<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 7/28/17
 * Time: 5:31 PM
 */

namespace yuanshuai\yscomponents\tencent;
require_once "qclound-sdk/src/QcloudApi/QcloudApi.php";
use QcloudApi;
use yii\base\Component;
use yii\helpers\Json;

/**
 * Class TencentComponent
 * @package yuanshuai\yscomponents\tencent
 */
class TencentComponent extends Component
{
    public $secretId;
    public $secretKey;
    public $region;

    /**
     * @var \QcloudApi_Module_Base
     */
    protected $service;
    protected $module;
    protected $method;

    public function init()
    {
        $config = [
            'SecretId'       => $this->secretId,
            'SecretKey'      => $this->secretKey,
            'RequestMethod'  => $this->method,
        ];
        if ($this->region) {
            $config['DefaultRegion']  = $this->region;
        }
        $this->service = QcloudApi::load($this->module,$config);
    }

    protected function request($action,$params)
    {
        $result = $this->service->$action($params);
        if ($result === false) {
            $error = $this->service->getError();
            \Yii::warning("请求腾讯接口错误:{$error->getMessage()}", __METHOD__);
            return [];
        }else{
            if (is_string($result)) {
                return Json::decode($result);
            }
            return $result;
        }
    }
}