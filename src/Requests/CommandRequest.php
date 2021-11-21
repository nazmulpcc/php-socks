<?php

declare(strict_types=1);

namespace Nazmul\Socks\Requests;

use Nazmul\Socks\Enums\AddressType;
use Nazmul\Socks\Enums\Command;

class CommandRequest extends Request
{
    /**
     * Type of the command
     *
     * @var int
     */
    protected $type;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * +----+-----+-------+------+----------+----------+
     *  |VER | CMD |  RSV  | ATYP | DST.ADDR | DST.PORT |
     *  +----+-----+-------+------+----------+----------+
     *  | 1  |  1  | X'00' |  1   | Variable |    2     |
     *  +----+-----+-------+------+----------+----------+
     *
     * @return void
     */
    protected function process()
    {
        $this->type = $this->nextByte(); // The command byte
        $this->nextByte(); // We skip the reserved byte
        $atype = $this->nextByte(); // Get the address type

        match($atype){
            AddressType::DOMAIN => $this->processDomainAddress(),
            AddressType::IPV4   => $this->processIpv4Address(),
            default  => $this->processIpv6Address()
        };
    }

    /**
     * Get the type of the request
     *
     * @return int
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * Get the host to connect to
     *
     * @return string
     */
    public function host()
    {
        return $this->host;
    }

    /**
     * Get the port to connect to
     *
     * @return int
     */
    public function port()
    {
        return $this->port;
    }

    /**
     * Check if the request is of the given type
     *
     * @param integer $type
     * @return boolean
     */
    public function is(int $type)
    {
        return $this->type === $type;
    }

    /**
     * Check if this is a connect request
     *
     * @return boolean
     */
    public function isConnectCommand()
    {
        return $this->is(Command::CONNECT);
    }

    /**
     * Check if this is a bind request
     *
     * @return boolean
     */
    public function isBindCommand()
    {
        return $this->is(Command::BIND);
    }

    /**
     * Check if this is an udp associate request
     *
     * @return boolean
     */
    public function isUdpAssociateCommand()
    {
        return $this->is(Command::UDP_ASSOCIATE);
    }

    protected function processDomainAddress()
    {
        $hostLength = $this->nextByte();
        $this->host = substr($this->data, 5, $hostLength);
        list(, $this->port) = unpack('n', substr($this->data, 5 + $hostLength, 2));
        $this->position = $this->length;
    }

    protected function processIpv4Address()
    {
        $this->host = long2ip(unpack('L', substr($this->data, 4, 4))[1]);
        $this->port = unpack('n', substr($this->data, -2))[1];
    }

    protected function processIpv6Address()
    {
        $this->host = inet_ntop(substr($this->data, 4, 16));
        $this->port = unpack('n', substr($this->data, -2))[1];
        
        throw new \Exception('IPV6 Is not yet supported');
    }
}
