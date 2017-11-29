<?php
$server = new swoole_websocket_server("0.0.0.0", 9501);

//设置配置
$server->set(
    array(
        'daemonize' => false,      // 是否是守护进程
        'max_request' => 10000,    // 最大连接数量
        'dispatch_mode' => 2,
        'debug_mode'=> 1,
        // 心跳检测的设置，自动踢掉掉线的fd
        'heartbeat_check_interval' => 5,
        'heartbeat_idle_time' => 600,
    )
);

$server->on('open', function (swoole_websocket_server $server, $request) {
    echo "服务器进行握手 fd{$request->fd}\n";
});

$server->on('message', function (swoole_websocket_server $server, $frame) {
    echo "接收数据 {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    //connections属性表示当前服务器的客户端连接
    foreach($server->connections as $fd) {
        $server->push($fd, $frame->data);
    }
});

$server->on('close', function ($server, $fd) {
    echo "服务器{$fd}关闭\n";
});

$server->start();