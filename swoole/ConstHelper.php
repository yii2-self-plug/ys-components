<?php
/**
 * Created by PhpStorm.
 * User: helong
 * Date: 2017/3/29
 * Time: 16:18
 */

namespace yuanshuai\yscomponents\swoole;


class ConstHelper
{
    //服务命令常量
    const RUN_START = 'start';//开始服务
    const RUN_STOP = 'stop';//停止服务
    const RUN_RESTART = 'restart';//重启服务
    const RUN_STATUS = 'status';//服务状态
    const RUN_HELP = 'help';//帮助
    const RUN_LIST = 'list';//任务列表

    //请求类型
    const SEND_STATUS = 'status'; //获取状态信息
    const SEND_TCP = 'tcp'; //直接返回
    const SEND_TASK = 'task'; //任务
    const SEND_TIMER = 'timer'; //定时器

    //websocket
    const WEB_SOCKET_HOST = '172.17.113.15';
    const WEB_SOCKET_PORT = '8008';
    const WEB_SOCKET_PID_FILE = '/tmp/websocket.pid';

    //定时器
    const TIMER_TICK = 'tick';//循环执行
    const TIMER_AFTER = 'after';//延迟执行
    const TIMER_CLEAR = 'clear';//清除定时器

    //默认设置
    const CONFIG_HOST = '127.0.0.1';
    const CONFIG_PORT = '8888';
    const CONFIG_PID_FILE = '/tmp/swoole.pid';
    const CONFIG_CLIENT_TIMEOUT = 30;
    const CONFIG_PROCESS_NAME = 'swoole';
}