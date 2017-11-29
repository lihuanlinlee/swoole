<?php
/*
 * Lession 1:创建一个最简单的server服务器
 * @Author dalin
 * @Date 2017/11/29
 */

//创建Server对象，监听 127.0.0.1:8088端口
$serv = new swoole_server("127.0.0.1", 8088); 

//监听连接进入事件
$serv->on('connect', function ($serv, $fd) 
{  
    echo "Client: Connect.\n";
});

//监听数据接收事件
$serv->on('receive', function ($serv, $fd, $from_id, $data) 
{
    $serv->send($fd, "Server: ".$data);
});

//监听连接关闭事件
$serv->on('close', function ($serv, $fd) 
{
    echo "Client: Close.\n";
});

//启动服务器
$serv->start(); 

/*
  新开一个终端，使用telnet连接到你的服务器：
telnet 127.0.0.1 8088
> hello world
> Server: hello world
 */