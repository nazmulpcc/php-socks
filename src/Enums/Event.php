<?php

declare(strict_types=1);

namespace Nazmul\Socks\Enums;

interface Event
{
    const START = 'start';

    const SHUTDOWN = 'shutdown';

    const CONNECT = 'connect'; // when a new connection is received by the TCP server

    const RECEIVE = 'receive'; // when data is received in a connection

    const REMOTE_CONNECTED = 'remote_connected'; // when a remote connection is established for proxing to a peer
    
    const REMOTE_FAILED = 'remote_failed'; // when a remote connection fails
    
    const AUTHENTICATED = 'authenticated'; // A peer has authenticated successfully

    const RECEIVED_PROXY_DATA = 'received_proxy_data'; // When data is received that needs to be proxied to a remote connection

    const SENT_DATA = 'sent_data'; // when data is sent to a peer

    const CLOSE = 'close';     // When a connection is closed
}