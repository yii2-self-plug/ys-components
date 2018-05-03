<?php
/**
 * Created by PhpStorm.
 * User: helong
 * Date: 2018/1/22
 * Time: 下午9:55
 */

namespace yuanshuai\yscomponents\weixin;
use EasyWeChat\Payment\LuckyMoney\API;
use EasyWeChat\Payment\Order;
use yii\base\Component;
use yuanshuai\yscomponents\weixin\models\Redpack;
use yuanshuai\yscomponents\weixin\models\Transfers;

/**
 * 微信组件
 * Class Weichat
 * @package yuanshuai\yscomponents\weixin
 */
class Wechat extends Component
{
    use WechatTrait;

    /**
     * 返回网页授权地址
     * @param $redirect
     * @return string
     */
    public function getAuthUrl($redirect)
    {
        return $this->app->oauth->redirect($redirect)->getTargetUrl();
    }

    /**
     * 判断是不是微信打开
     * @return bool
     */
    public function getIsWechat()
    {
        return strpos($_SERVER["HTTP_USER_AGENT"], "MicroMessenger") !== false;
    }

    /**
     * 返回用户信息
     * @return $this|\Overtrue\Socialite\User
     */
    public function getUser()
    {
        return $this->app->oauth->user();
    }

    /**
     * 返回微信API
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getServer()
    {
        return $this->app->server->serve();
    }

    /**
     * 根据小程序CODE获取OPENID信息
     * @param $code
     * @return \EasyWeChat\Support\Collection
     */
    public function getSession($code)
    {
        return $this->app->mini_program->sns->getSessionKey($code);
    }

    /**
     * 微信支付
     * @param Order $order
     * @return \EasyWeChat\Support\Collection
     */
    public function pay(Order $order)
    {
        return $this->app->payment->prepare($order);
    }

    /**
     * 发红包
     * @param Redpack $redpack
     * @param string $type
     * @return bool|\EasyWeChat\Support\Collection
     */
    public function redpack(Redpack $redpack,$type = API::TYPE_NORMAL)
    {
        if ($redpack->validate()) {
            return $this->app->lucky_money->getAPI()->send($redpack->getAttributes(),$type);
        }
        return false;
    }

    /**
     * @param Transfers $transfers
     * @return bool|\EasyWeChat\Support\Collection
     */
    public function sendMoney(Transfers $transfers)
    {
        if (!$transfers->validate()) {
            return false;
        }
        $params = $transfers->getAttributes();
        if ($transfers->check_name == Transfers::NOT_CHECK_USER_NAME) {
            unset($params["re_user_name"]);
        }
        return $this->app->merchant_pay->getAPI()->send($params);
    }
}