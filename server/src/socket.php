<?php

use OpenSwoole\WebSocket\{Frame, Server};
use OpenSwoole\Constant;
use OpenSwoole\Http\Request;
use OpenSwoole\Table;
use Services\App;
use Services\Redis;
use Services\Scraping;

$server = new Server("localhost", 8085, Server::SIMPLE_MODE, Constant::SOCK_TCP);

$fds = new Table(1024);
$fds->column('fd', Table::TYPE_INT, 4);
$fds->column('name', Table::TYPE_STRING, 16);
$fds->column('date', Table::TYPE_STRING, 16);
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

    echo $date;
    if (!preg_match('/^\d{8}$/', $date)) {
        $server->push($frame->fd, "Invalid input.");
        return;
    }

    $fd = $frame->fd;
    $name = $fds->get($frame->fd, "name");

    $fds->set($fd, [
        'fd' => $fd,
        'name' => $name,
        'date' => $date
    ]);

    $result=json_encode(getResult($date));

    $server->push($fd, $result);
});

$server->on('Close', function (Server $server, int $fd) use ($fds) {
    $date = $fds->get('fd', $fd)['date'];
    disconnectDate($date);

    $fds->del($fd);
    echo "Connection close: {$fd}, total connections: " . $fds->count() . "\n";
});

$server->on('Disconnect', function (Server $server, int $fd) use ($fds) {
    $date = $fds->get('fd', $fd)['date'];
    disconnectDate($date);

    $fds->del($fd);
    echo "Disconnect: {$fd}, total connections: " . $fds->count() . "\n";
});

$server->start();

function checkTheDate($date)
{
    $redis = App::resolve(Redis::class);
    $key = "date:$date";

    if ($redis->exists($key)) {
        $redis->incr($key);
    } else {
        $redis->set($key, 0);
    }
}

function getResult($date)
{
    $redis = App::resolve(Redis::class);
    $scraping = App::resolve(Scraping::class);

    checkTheDate($date);

    $key = "date:$date";

    if($redis->get($key)==0){
        $results=$scraping->getScores($date);
        $redis->storeAllScores($results,$date);
    }

    return $redis->getAllScores($date);
}

function disconnectDate($date)
{
    $redis = App::resolve(Redis::class);
    $key = "date:$date";
    $redis->decr($key);
}