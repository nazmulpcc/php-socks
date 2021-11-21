<?php

declare(strict_types=1);

namespace Nazmul\Socks;

use Swoole\Client as TcpClient;

class Peer
{
    /**
     * Remote Tcp client for this peer
     *
     * @var TcpClient
     */
    protected $remote;

    protected $open = false;


    /**
     * Keep track whether this peer has completed authentication
     *
     * @var boolean
     */
    protected $authenticated = false;

    public function __construct(protected Server &$server, protected int $fd)
    {
        $this->remote = new TcpClient(SWOOLE_SOCK_TCP);
    }

    /**
     * Check if the peer is authenticated & connected to a remote server
     *
     * @return boolean
     */
    public function isConnected(): bool
    {
        return $this->remote->isConnected();
    }

    /**
     * Connect to the given $host and $port
     *
     * @param string $host
     * @param integer $port
     * @param integer $timeout
     * @return boolean
     */
    public function connect(string $host, int $port, $timeout = 1)
    {
        if($this->remote->connect($host, $port, $timeout)){
            return $this->open = true;
        }
        return false;
    }

    /**
     * Close the connection
     *
     * @return void
     */
    public function close()
    {
        // TODO: handle more events
        $this->open = false;
    }

    /**
     * Check if this peer has completed authentication
     *
     * @return boolean
     */
    public function hasAuthenticated(): bool
    {
        return $this->authenticated;
    }

    /**
     * Mark this peer as authenticated
     *
     * @return void
     */
    public function markAsAuthenticated(): void
    {
        $this->authenticated = true;
    }

    /**
     * Handle incomming data intended to be proxied to remote server
     *
     * @param mixed $data
     * @return void
     */
    public function handle(mixed $data)
    {
        if(!$this->isConnected()){
            throw new \Exception("Remote TCP connection is not open");
        }
        $this->remote->send($data);

        \Swoole\Event::add($this->remote, function ($client) {
            while ($bytes =  $client->recv()) {
                $this->server->send($this, $bytes);
            }
        });
    }

    /**
     * Get the underlying file descriptor id provided by swoole
     *
     * @return integer
     */
    public function getFd(): int
    {
        return $this->fd;
    }
}