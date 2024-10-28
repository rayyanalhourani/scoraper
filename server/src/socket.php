<?php

use OpenSwoole\WebSocket\{Frame, Server};
use OpenSwoole\Constant;
use OpenSwoole\Http\Request;
use OpenSwoole\Table;
use Services\App;
use Services\Redis;
use Services\Scraping;

$redis = App::resolve(Redis::class);
$scraping = App::resolve(Scraping::class);

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

    if (!preg_match('/^\d{8}$/', $date)) {
        $server->push($frame->fd, "Invalid input.");
        return;
    }

    $fd = $frame->fd;
    $name = $fds->get($frame->fd, "name");
    connectUser($date, $fd, $name);

    $result = json_encode(getResult($date));

    $server->push($fd, $result);
});

$server->on('Close', function (Server $server, int $fd) use ($fds) {
    $fdData = $fds->get($fd);
    $date = $fdData['date'] ?? null;
    if ($date !== null) {
        disconnectUser($date);
    }

    $fds->del($fd);
    echo "Connection close: {$fd}, total connections: " . $fds->count() . "\n";
});

$server->on('Disconnect', function (Server $server, int $fd) use ($fds) {
    $fdData = $fds->get($fd);
    $OldDate = $fdData['date'] ?? null;
    if ($OldDate !== null) {
        disconnectUser($OldDate);
    }

    $fds->del($fd);
    echo "Disconnect: {$fd}, total connections: " . $fds->count() . "\n";
});

$server->tick(5000, function () use ($fds, $redis,$server) {
    $keys = $redis->findKeysByPattern("date:*");

    $current_time = new DateTime();
    $current_date = $current_time->format('Ymd');

    foreach ($keys as $key) {
        $date = substr($key, 5);

        if ($date >= $current_date && $redis->get("users:$date")>0) {
            $time_string = $redis->get("time:$date");
            
            $event_time = DateTime::createFromFormat('H:i', $time_string);

            $diff = $current_time->diff($event_time);
            $minutes_diff = ($diff->h * 60) + $diff->i;

            if ($minutes_diff >= 5) {
                storeInCache($date);

                $fdList=$fds->get("date",$date);
                $result = $redis->getAllScores($date);
                foreach($fdList as $fd){
                    $result = json_encode($result);
                    $server->push($fd, $result);
                }
            }
        }
    }
});      

$server->start();

function getResult($date)
{
    global $redis;

    if(!$redis->exists("date:$date")){
        storeInCache($date);
    }

    return $redis->getAllScores($date);
}

function connectUser($date, $fd, $name)
{
    global $redis, $fds;

    $fdData = $fds->get($fd);
    $OldDate = $fdData['date'] ?? null;
    if ($OldDate != null) {
        disconnectUser($OldDate);
    }

    $fds->set($fd, [
        'fd' => $fd,
        'name' => $name,
        'date' => $date
    ]);

    $key = "users:$date";
    $redis->incr($key);
}
function disconnectUser($date)
{
    global $redis;
    $key = "users:$date";
    $redis->decr($key);
}

function storeInCache($date){
    global $redis, $scraping;

    $current_time = (new DateTime())->format('H:i');

    $results=$scraping->getScores($date);
    $redis->set("date:$date",1);
    $redis->set("time:$date",$current_time);
    $redis->storeAllScores($results,$date);
}
