<?php

use Services\App;
use Services\Container;
use Services\Redis;
use Services\Scraping;

$container = new Container();

$container->bind('Services\Redis', function () {
    return new Redis("tcp", "127.0.0.1", 6379);
});

$container->bind('Services\Scraping', function () {
    return new Scraping("ws://localhost:3000");
});

App::setContainer($container);
