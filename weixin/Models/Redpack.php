<?php
namespace yuanshuai\yscomponents\weixin\models;
use yii\base\Model;

/**
 * Created by PhpStorm.
 * User: helong
 * Date: 2018/2/25
 * Time: 下午8:25
 */
class Redpack extends Model
{
    public $mch_billno;
    public $send_name;
    public $re_openid;
    public $total_num;
    public $total_amount;
    public $wishing;
    public $client_ip;
    public $act_name;
    public $remark;

    public function rules()
    {
        return [
            [["mch_billno","send_name","re_openid","total_amount"],"required"]
        ];
    }
}