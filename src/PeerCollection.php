<?php

declare(strict_types=1);

namespace Nazmul\Socks;

class PeerCollection
{
    /**
     * Collection of peers
     *
     * @var Peer[]
     */
    protected array $peers = [];

    public function __construct(protected Server &$server)
    {
        //
    }

    /**
     * Add a new Peer created from the given $fd
     *
     * @param int $fd
     * @return Peer
     */
    public function add(int $fd)
    {
        $this->peers[$fd] = new Peer($this->server, $fd);

        return $this->peers[$fd];
    }

    /**
     * Get the Peer with the given $fd
     *
     * @param int $fd
     * @return Peer
     */
    public function get(int $fd): Peer
    {
        return $this->peers[$fd] ?? $this->add($fd);
    }

    public function has(int $fd)
    {
        return isset($this->peers[$fd]);
    }

    public function forget(int $fd)
    {
        $this->get($fd)->close();
        unset($this->peers[$fd]);
    }
}

