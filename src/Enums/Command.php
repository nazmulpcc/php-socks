<?php

declare(strict_types=1);

namespace Nazmul\Socks\Enums;

interface Command {
    const CONNECT = 1;

    const BIND = 2;

    const UDP_ASSOCIATE = 3;
}