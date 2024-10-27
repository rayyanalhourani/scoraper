<?php

use Service\App;
use Service\Container;
use Service\Redis;
use Service\Scraping;

$container = new Container();

$container->bind('Service\Redis', function () {
    return new Redis("tcp", "127.0.0.1", 6379);
});

$container->bind('Service\Scraping', function () {
    return new Scraping("http://10.123.41.244:4444/");
});
