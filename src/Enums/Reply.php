<?php

declare(strict_types=1);

namespace Nazmul\Socks\Enums;

interface Reply
{
    const SUCCESS = 0;

    const SERVER_FAILURE = 1;

    const NOT_ALLOWED = 2;

    const NETWORK_UNREACHABLE = 3;

    const HOST_UNREACHABLE = 4;

    const REFUSED = 5;

    const TTL_EXPIRED = 6;

    const UNSUPPORTED_COMMAND = 7;

    const UNSUPPORTED_ADDRESS = 8;
}