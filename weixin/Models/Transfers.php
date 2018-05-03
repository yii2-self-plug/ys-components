<?php
/**
 * Created by PhpStorm.
 * User: helong
 * Date: 2018/3/10
 * Time: 下午6:16
 */

namespace yuanshuai\yscomponents\weixin\models;


use yii\base\Model;

/**
 * 退款金额模型
 * Class Transfers
 * @package yuanshuai\yscomponents\weixin\models
 */
class Transfers extends Model
{
    const NEED_CHECK_USER_NAME = "FORCE_CHECK";
    const NOT_CHECK_USER_NAME = "NO_CHECK";
    public $partner_trade_no;
    public $openid;
    public $check_name;
    public $re_user_name;
    public $amount;
    public $desc;
    public $spbill_create_ip;

    public function rules()
    {
        return [
            [["partner_trade_no","openid","check_name","amount","desc"],"required"],
            [["re_user_name"],"checkUserName","skipOnEmpty" => false]
        ];
    }

    public function checkUserName($attr,$params)
    {
        if ($this->check_name == self::NEED_CHECK_USER_NAME) {
            if (empty($this->re_user_name)) {
                $this->addError($attr,"用户名不能为空");
                return false;
            }
        }
    }

    public function beforeValidate()
    {
        if (empty($this->spbill_create_ip)) {
            $this->spbill_create_ip = $_SERVER["SERVER_ADDR"];
        }
        return parent::beforeValidate(); // TODO: Change the autogenerated stub
    }
}