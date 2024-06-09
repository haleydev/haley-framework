<?php

namespace Haley\Server\WebSocket;

use Swoole\WebSocket\Server;
use Swoole\Http\Request;

class WebSocket
{
    protected Server|null $server = null;
    protected Request|null $request = null;
    protected int|null $fd = null;

    public function __construct(int $fd, Server $server, Request|null $request = null)
    {
        $this->fd = $fd;
        $this->server = $server;
        $this->request = $request;

        return $this;
    }

    /**
     * Get all clients ids
     */
    public function clients(bool $self = true)
    {
        $clients = [];

        foreach ($this->server->connections as $fd) {
            if (!$self && $fd == $this->fd) continue;

            $clients[] = $fd;
        }

        return $clients;
    }

    /**
     * Send data
     */
    public function send(int|array $id, string $data, bool $bynary = false)
    {
        $result = true;

        if (!is_array($id)) $id = [$id];
        if (!count($id)) return false;

        $opcode = $bynary ? WEBSOCKET_OPCODE_BINARY : WEBSOCKET_OPCODE_TEXT;

        foreach ($id as $fd) if (!$this->server->push($fd, $data, $opcode)) $result = false;

        return $result;
    }

    /**
     * Get client info
     * @return array|null
     */
    public function clientInfo(int $id)
    {
        $info = $this->server->getClientInfo($id);

        if (!$info) return null;

        return [
            'id' => $id,
            'remote_port' => $info['remote_port'],
            'remote_ip' => $info['remote_ip'],

            'connect_time' => $info['connect_time'],
            'last_time' => $info['last_time'],
            'last_recv_time' => $info['last_recv_time'],
            'last_send_time' => $info['last_send_time'],
        ];
    }

    public function online(int $id)
    {
        return $this->server->isEstablished($id);
    }

    /**
     * Get the number of connections
     * @return int
     */
    public function connections()
    {
        return $this->server->stats()['connection_num'] ?? 0;
    }

    /**
     * Close the connection
     */
    public function close(int|array $id)
    {
        $result = true;

        if (!is_array($id)) $id = [$id];

        foreach ($id as $fd) if (!$this->server->close($fd, WEBSOCKET_CLOSE_NORMAL)) $result = false;

        return $result;
    }
}
