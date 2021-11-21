<?php

declare(strict_types=1);

namespace Nazmul\Socks\Enums;

interface AddressType
{
    const IPV4 = 1;

    const DOMAIN = 3;

    const IPV6 = 4;
}