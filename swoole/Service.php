<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 6/13/17
 * Time: 2:31 PM
 */

namespace yuanshuai\yscomponents\swoole;


use yuanshuai\yscomponents\swoole\client\Client;

class Service
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var
     */
    protected $group;

    function __construct()
    {
        $this->client = new Client($this->group);
    }

    function __call($name, $arguments)
    {
        return call_user_func([$this->client,$name],$arguments);
    }
}