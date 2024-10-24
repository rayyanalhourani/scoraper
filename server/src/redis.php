<?php

use Predis\Client;

$client = new Client([
    'scheme' => 'tcp',
    'host'   => '127.0.0.1',
    'port'   => 6379,
]);

// Check the connection
if ($client->ping()) {
    error_log("Connected to Redis server.");
} else {
    error_log("Failed to connect to Redis.");
}
