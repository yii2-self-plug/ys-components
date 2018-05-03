<?php
/**
 * Created by PhpStorm.
 * User: 0489617
 * Date: 2017/12/4
 * Time: 15:47
 */

namespace yuanshuai\yscomponents\redis;
use yii\base\Component;

/**
 * redis扩展（C）
 *
 * Class RedisConnection
 * @package yuanshuai\yscomponents\redis
 */
class RedisConnection extends Component
{
    public $host;
    public $prod;
    public $database = 0;
    public $password = null;
    public $pconnect = false;

    private $redis;

    public function init()
    {
        $this->redis = new \Redis();
        if ($this->pconnect) {
            $this->redis->pconnect($this->host,$this->prod);
        }else{
            $this->redis->connect($this->host,$this->prod);
        }
    }

    public function __call($name, $arguments)
    {
        return call_user_func([$this->redis,$name],$arguments);
    }
}