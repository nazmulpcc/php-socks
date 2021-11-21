<?php

require_once __DIR__ . '/vendor/autoload.php';

use Nazmul\Socks\Enums\Event;
use Nazmul\Socks\Peer;
use Nazmul\Socks\Server;

try {
    $server = new Server('0.0.0.0', 1080);

    $server->on(Event::START, function ($server){
        echo "Server started\n";
    });

    $server->on(Event::AUTHENTICATED, function (Peer $peer)
    {
        echo "Client #". $peer->getFd() . " has authenticated\n";
    });

    $server->on(Event::CLOSE, function ($server, $fd, $reactor) {
        echo "Connection closed with client #{$fd}.\n";
    });

    $server->start();

}catch (\Exception $e){
    echo "Failed to proxy\n";
}
