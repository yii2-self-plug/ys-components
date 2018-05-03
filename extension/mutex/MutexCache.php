<?php
/**
 * Created by PhpStorm.
 * User: 0489617
 * Date: 2018/2/2
 * Time: 10:24
 */

namespace yuanshuai\yscomponents\extension\mutex;
use yii\caching\Cache;
use yii\mutex\Mutex;

/**
 * Class MutexCache
 * @package yuanshuai\yscomponents\extension\mutex
 */
class MutexCache extends Mutex
{
    /**
     * @var Cache $cache
     */
    public $cache;

    public function init()
    {
        if (empty($this->cache)) {
            $this->cache = \Yii::$app->getCache();
        }
    }

    protected function acquireLock($name,$timeout = 0)
    {
        return $this->cache->add($name,$timeout);
    }

    protected function releaseLock($name)
    {
        return $this->cache->delete($name);
    }

    /**
     * 加锁
     * @param $name
     * @param int $timeout
     * @return bool
     */
    public function lock($name,$timeout = 0)
    {
        return $this->acquire($name,$timeout);
    }

    /**
     * 释放锁
     * @param $name
     * @return bool
     */
    public function unLock($name)
    {
        return $this->release($name);
    }
}