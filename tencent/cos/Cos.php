<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 7/28/17
 * Time: 6:02 PM
 */

namespace yuanshuai\yscomponents\tencent\cos;
require_once "cos-sdk/include.php";
use QCloud\Cos\Api;
use yii\base\Component;
class Cos extends Component
{
    public $appId;
    public $secretId;
    public $secretKey;
    public $region;
    public $timeout = 60;
    public $bucket;

    /**
     * @var Api $api
     */
    protected $api;
    public function init()
    {
        $config = [
            'app_id' => $this->appId,
            'secret_id' => $this->secretId,
            'secret_key' => $this->secretKey,
            'region' => $this->region,
            'timeout' => $this->timeout
        ];

        $this->api = new Api($config);
    }

    public function upload($path,$dstPath)
    {
        return $this->api->upload($this->bucket,$path,$dstPath);
    }
}