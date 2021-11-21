<?php
declare(strict_types=1);

namespace Nazmul\Socks;

use Nazmul\Socks\Enums\AddressType;
use Nazmul\Socks\Enums\Event;
use Nazmul\Socks\Enums\Reply;
use Nazmul\Socks\Requests\AuthenticationRequest;
use Nazmul\Socks\Requests\CommandRequest;
use Swoole\Server as TcpServer;

/**
 * Socks5 proxy server as defined in RFC1928
 * https://datatracker.ietf.org/doc/html/rfc1928
 */
class Server
{
    use HasEvents;

    /**
     * Underlying Swoole TCP server
     *
     * @var TcpServer
     */
    protected TcpServer $server;

    /**
     * Collection of peers connected to the proxy server
     *
     * @var PeerCollection
     */
    protected PeerCollection $peers;

    public function __construct(protected string $host, protected int $port)
    {
        $this->server = new TcpServer($this->host, $this->port);
        $this->peers = new PeerCollection($this);
        
        // Event management
        $this->forwardTcpServerEvents();
        $this->server->on(Event::RECEIVE, [$this, 'handleRequest']);
        $this->server->on(Event::CLOSE, function ($server, $fd, $reactor_id) {
            $this->peers->forget($fd);
        });
    }

    public function handleRequest(TcpServer $server, int $fd, int $reactor_id, $data)
    {
        // Get existing peer or create a new one based on $fd
        $peer = $this->peers->get($fd);

        /**
         * If the peer is already connected to a remote connection
         * It means we've already completed the authentication & initiated a connection
         * So the received data is directly proxied to the intended destination via the underlying TCP client
         * Last para of https://datatracker.ietf.org/doc/html/rfc1928#section-6
         */
        if($peer->isConnected()){
            $this->fire(Event::RECEIVED_PROXY_DATA, $peer, strlen($data));
            return $peer->handle($data);
        }

        /**
         * We check if the peer is already authenticated<br>
         * If not, this must be an authentication request
         * https://datatracker.ietf.org/doc/html/rfc1928#section-3
         */
        if(!$peer->hasAuthenticated()){
            return $this->authenticate($peer, $data);
        }

        /**
         * But if the peer is already authenticated, then this is a command request
         * https://datatracker.ietf.org/doc/html/rfc1928#section-4
         */

        return $this->handleCommandRequest($peer, $data);
    }

    public function handleCommandRequest(Peer &$peer, &$data)
    {
        $request = new CommandRequest($data);
        
        if($request->isConnectCommand()){
            $peer->connect($request->host(), $request->port());
            return $this->reply($peer, Reply::SUCCESS);
        }

        if($request->isBindCommand()){
            // Bind requests are described here -> https://datatracker.ietf.org/doc/html/rfc1928#section-6
            // Bind requestes are not yet supported
            return $this->reply($peer, Reply::UNSUPPORTED_ADDRESS);
        }

        if ($request->isUdpAssociateCommand()) {
            // UDP associate requests are described here -> https://datatracker.ietf.org/doc/html/rfc1928#section-6
            // UDP requestes are not yet supported
            return $this->reply($peer, Reply::UNSUPPORTED_ADDRESS);
        }
    }

    /**
     * Handle authentication request from a peer
     *
     * @param Peer $peer
     * @param mixed $data
     * @return void
     */
    public function authenticate(Peer $peer, mixed &$data)
    {
        $request = new AuthenticationRequest($data);

        // TODO: Handle password authentication
        $peer->markAsAuthenticated();
        $this->fire(Event::AUTHENTICATED, $peer);
        return $this->send($peer, pack('C*', 0x5, 0x0));
    }

    /**
     * Start the Socks proxy server
     *
     * @return void
     */
    public function start()
    {
        $this->server->start();

        // Fire the server start event
        $this->fire(Event::START, $this);
    }

    /**
     * Send data to a specific peer<br>
     * Usually this is called when a peer receives data from a remote server
     *
     * @param Peer $peer
     * @param mixed $data
     * @return void
     */
    public function send(Peer &$peer, $data)
    {
        $this->server->send($peer->getFd(), $data);
        $this->fire(Event::SENT_DATA, $peer, strlen($data));
    }

    /**
     * Send reply to a command request
     * 
     * +----+-----+-------+------+----------+----------+
     * |VER | REP |  RSV  | ATYP | BND.ADDR | BND.PORT |
     * +----+-----+-------+------+----------+----------+
     * | 1  |  1  | X'00' |  1   | Variable |    2     |
     * +----+-----+-------+------+----------+----------+
     *
     * @param int $status
     * @param string|bool $host
     * @param boolean|string $port
     * @return void
     */
    public function reply(Peer &$peer, $status)
    {
        $response = chr(5); // sock version
        $response .= chr($status); // rep
        $response .= chr(0); // rsv
        $response .= chr(AddressType::IPV4); // TODO: handle other type of bind addresses
        foreach (explode('.', $this->host) as $block) {
            $response .= chr((int) $block);
        }
        $response .= pack('n', $this->port);

        return $this->send($peer, $response);
    }

    /**
     * Forward TCP server events to our own event manager
     * @return void
     */
    protected function forwardTcpServerEvents()
    {
        $this->server->on(Event::SHUTDOWN, fn(...$args) => $this->fire(Event::SHUTDOWN, ...$args));
        $this->server->on(Event::CLOSE, fn (...$args) => $this->fire(Event::CLOSE, ...$args));
        // forward more events ?
    }
}
