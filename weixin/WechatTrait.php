<?php
/**
 * Created by PhpStorm.
 * User: 0489617
 * Date: 2018/1/23
 * Time: 16:31
 */

namespace yuanshuai\yscomponents\weixin;
use EasyWeChat\Foundation\Application;

trait WechatTrait
{
    public $appId;
    public $secret;
    public $token;
    public $responseType = "array";

    public $merchant_id;
    /**
     * @var Application $app
     */
    protected $app;
    public $log;

    public $cert_path;
    public $key_path;
    public function init()
    {
        if (empty($this->log)) {
            $this->log = [
                'level' => 'error',
                'file' => \Yii::getAlias("@runtime/logs/wechat".date("Ymd",time()).".log"),
            ];
        }
        if (empty($this->cert_path)) {
            $this->cert_path = \Yii::getAlias("@common/config/key/apiclient_cert.pem");
        }
        if (empty($this->key_path)) {
            $this->key_path = \Yii::getAlias("@common/config/key/apiclient_key.pem");
        }
        $this->app = new Application([
            "app_id"=>$this->appId,
            "secret"=>$this->secret,
            "token"=>$this->token,
            "response_type"=>$this->responseType,
            "log"=>$this->log,
            "payment"=>[
                "merchant_id"=>$this->merchant_id,
                "key"=>$this->secret,
                "cert_path"=> $this->cert_path,
                "key_path" => $this->key_path
            ],
            "mini_program"=>[
                'app_id'=>$this->appId,
                'secret'=>$this->secret
            ]
        ]);
        parent::init();
    }
}