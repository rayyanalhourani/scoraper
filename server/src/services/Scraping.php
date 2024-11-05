<?php

namespace Services;

use Exception;

class Scraping
{
    public static $client;
    public function __construct($host)
    {
        self::$client = new \WebSocket\Client($host);
        self::$client->setTimeout(0);        
    }

    public function __destruct(){
        self::$client->close();
    }

    public function getScores($date) {
        self::$client->text(json_encode(["date"=>$date]));
        $result = json_decode(self::$client->receive(),true);
        return $result["scores"];
    }
}
