## YII2+SWOOLE使用说明

### 安装SWOOLE
 - [swoole管网](http://www.swoole.com/)
 - pecl安装
```
#/usr/local/php/bin/pecl install swoole
```
 - 编译安装
```
git clone https://github.com/swoole/swoole-src.git
cd swoole-src
phpize
./configure
make && make install
```
### 配置YII2
 - 服务端配置:
```
...
'components'=>[
        'server'=>[
            'class'=>'yuanshuai\yscomponents\swoole\ServerComponent',
            'config'=>[
                'host'=>'127.0.0.1',
                'port'=>'8888',
                'client_timeout'=>30,
                'pidfile'=>'/tmp/swoole.pid'
                'setting'=>[
                    'log_file'=>Yii::getAlias("@server/runtime/server.log"),
                ]
            ]
        ],
    ],
...
```
 - 服务端配置说明：
```
host:服务启动IP
port:服务启动端口
client_timeout:客户端连接超时时间
pidfile:服务进程启动PID记录文件
setting['log_file']:日志记录文件
```
 - 客服端配置：
```
'params'=>[
    'serverClient'=>[
        'default'=>[
            'host'=>'127.0.0.1',
            'port'=>'8888',
            'client_timeout'=>30
        ]
    ]
]
```
 - 客户端配置说明
```
default:默认配置
host:服务端IP地址
prot:服务端端口号
client_timeout:客户端连接超时时间
```
### 服务端
 - 创建服务端：继承yuanshuai\yscomponents\swoole\console\SwooleController
 - 服务使用
```
class xxxController extends SwooleController {

}
使用console开启服务：
./XXX/index.php xxx/run start
命令参数:
start:开启服务
stop:停止服务
restart:重启服务
status:服务状态
```
- 构建服务操作：使用console般的MC结构，然后直接在action里return数据就可以了。
### 客户端
- 使用方式
```
/**
 * test对应服务端的Controller
 */
$client = new yuanshuai\yscomponents\swoole\client\Client("test");
/**
 * test()对应服务端的action，参数为服务端action需要的参数
 */
$result = $client->test(["id"=>123,"name"=>"张三"]);
```
## WebSocket
### 服务端
- 配置服务端
```
...
'components'=>[
    'websocket'=>[
        'class'=>'yuanshuai\yscomponents\swoole\WebSocketComponent',
        'config'=>[
            'host'=>'127.0.0.1',
            'port'=>'8008',
            'pidfile'=>'/tmp/websocket.pid',
            'setting'=>[
                'daemonize'=>true
            ]
        ]
    ]
]
...
```
- 配置说明
```
host:服务端IP地址
port:服务端端口
pidfile:记录服务端启动PID的文件
setting['daemonize']:是否守护进程启动
```
- 构造服务端

```

/**
 * 创建服务端，继承\yuanshuai\yscomponents\swoole\console\WebSocketController
 */
use \yuanshuai\yscomponents\swoole\console\WebSocketController as Controller;
class WebSocketController extends Controller
{
    /**
     * 客户端链接上的时候回调函数
     * @param \swoole_websocket_server $server
     * @param $request
     */
    protected function open(\swoole_websocket_server $server, $request)
    {
        echo "连接打开了";
    }

    /**
     * 服务端收到客户端消息时候的回调函数
     * @param \swoole_websocket_server $server
     * @param $frame
     */
    protected function message(\swoole_websocket_server $server, $frame)
    {
        echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
        foreach ($server->connections as $fd) {
            $server->push($fd, "this is messge:{$frame->data}");
        }
    }

    /**
     * 客户端断开链接时候的回调函数
     * @param $ser
     * @param $fd
     */
    protected function close($ser, $fd)
    {

    }

    /**
     * 当使用request请求时候当回调函数
     * @param $request
     * @param $response
     */
    protected function request($request, $response)
    {

    }
}

```
### 客户端，自己研究