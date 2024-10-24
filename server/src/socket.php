<?php 

use OpenSwoole\WebSocket\{Frame, Server};
use OpenSwoole\Constant;
use OpenSwoole\Http\Request;
use OpenSwoole\Table;

$server = new Server("127.0.0.1", 8085, Server::SIMPLE_MODE, Constant::SOCK_TCP);

$fds = new Table(1024);
$fds->column('fd', Table::TYPE_INT, 4);
$fds->column('name', Table::TYPE_STRING, 16);
$fds->column('poll_id', Table::TYPE_STRING, 4);
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
    echo "Connection <{$fd}> open by {$clientName}. Total connections: " . $fds->count() . "\n";
    foreach ($fds as $key => $value) {
        if ($key == $fd) {
            $server->push($request->fd, "Welcome {$clientName}, there are " . $fds->count() . " connections");
        } else {
            $server->push($key, "A new client ({$clientName}) is joining to the party");
        }
    }
});

$server->on('Message', function (Server $server, Frame $frame) use ($fds) {
    $data = json_decode($frame->data, true);

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
