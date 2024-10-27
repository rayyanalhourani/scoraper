<?php

namespace Service;
use Predis\Client;


class Redis
{
    public $redis;

    public function __construct($scheme, $host, $port)
    {
        $this->redis = new Client([
            'scheme' => $scheme,
            'host'   => $host,
            'port'   => $port,
        ]);

        if ($this->redis->ping()) {
            error_log("Connected to Redis server.");
        } else {
            error_log("Failed to connect to Redis.");
        }
    }

    function storeAllScores($scores, $date)
    {
        global $redis;

        foreach ($scores as $leagueName => $matches) {
            foreach ($matches as $match) {
                $team1 = $match['teams'][0]['name'];
                $team2 = $match['teams'][1]['name'];

                $key = "$date:$leagueName:$team1:$team2";

                $redis->hMSet($key, [
                    'date' => $match['date'],
                    'stadium' => $match['stadium'],
                    'city' => $match['city'],
                    'status' => $match['TimeOrStatus'],
                    'team1' => json_encode($match['teams'][0]),
                    'team2' => json_encode($match['teams'][1])
                ]);
            }
        }
    }

    function findKeysByPattern($pattern)
    {
        global $redis;
        return $redis->keys($pattern);
    }

    function findByKey($key)
    {
        global $redis;
        return $redis->hGetAll($key);
    }

    function findAllByKeys($keys)
    {
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $this->findByKey($key);
        }
        return $results;
    }
}
