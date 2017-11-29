<?php
/*
 * Lession 1:创建一个最简单的异步client客户端
 * @Author dalin
 * @Date 2017/11/29
 * @Desc 异步的客户端是通过监听具体动作来进行数据的传输
 */

/*
 * 构建socket客户端，第一个参数是socket的类型，目前支持SWOOLE_SOCK_UDP/SWOOLE_SOCK_TCP。第二个参数SWOOLE_SOCK_SYNC表示是同步阻塞来执行,
 * SWOOLE_SOCK_ASYNC表示异步非阻塞客户端
 */
$client = new swoole_client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_ASYNC);

$client->on("connect", function(swoole_client $cli) {
     $cli->send("Hello world");
});
$client->on("receive", function(swoole_client $cli, $data){
    echo "Receive: $data \n";
    
    //关闭客户端
    $cli->close();
});
$client->on("error", function(swoole_client $cli){
    echo "error\n";
});
$client->on("close", function(swoole_client $cli){
    echo "Connection close\n";
});

//connect方法用来连接到Server，参数分别是Host、Port、超时时间单位是秒0.5表示500ms、是否启用UDP connect
if (!$client->connect('127.0.0.1', 8088, -1))
{
    exit("connect failed. Error: {$client->errCode}\n");
}


