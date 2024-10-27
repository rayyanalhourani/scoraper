<?php

use OpenSwoole\WebSocket\{Frame, Server};
use OpenSwoole\Constant;
use OpenSwoole\Http\Request;
use OpenSwoole\Table;

use Services\App;
use Services\Redis;

$redis = App::resolve(Redis::class);

$server = new Server("localhost", 8085, Server::SIMPLE_MODE, Constant::SOCK_TCP);

$fds = new Table(1024);
$fds->column('fd', Table::TYPE_INT, 4);
$fds->column('name', Table::TYPE_STRING, 16);
$fds->create();

$server->on("Start", function (Server $server) {
    echo "Swoole WebSocket Server is started at " . $server->host . ":" . $server->port . "\n";
});

$server->on('Open', function (Server $server, Request $request) use ($fds) {
    $fd = $request->fd;
    $clientName = sprintf("Client-%'.06d\n", $request->fd);
    $fds->set($request->fd, [
        'fd' => $fd,
        'name' => sprintf($clientName)
    ]);
    $server->push($fd, "Welcome {$clientName}");
    echo "Connection <{$fd}> open by {$clientName}. Total connections: " . $fds->count() . "\n";
});

$server->on('Message', function (Server $server, Frame $frame) use ($fds) {
    $date = $frame->data;

    if (!preg_match('/^\d{8}$/', $date)) {
        $server->push($frame->fd, "Invalid input.");
        return;
    }
});

$server->on('Close', function (Server $server, int $fd) use ($fds) {
    $fds->del($fd);
    echo "Connection close: {$fd}, total connections: " . $fds->count() . "\n";
});

$server->on('Disconnect', function (Server $server, int $fd) use ($fds) {
    $fds->del($fd);
    echo "Disconnect: {$fd}, total connections: " . $fds->count() . "\n";
});

$server->start();

function checkDate($date){

}