<?php

namespace Haley\WebSocket;

use Haley\Console\Lines;

class SocketClient
{
    public function send(string $data, int|array|null $client_id = null)
    {
        if (is_int($client_id)) $client_id = [$client_id];
        elseif (is_null($client_id)) $client_id = $this->ids();


        // if (!mb_check_encoding($data, 'UTF-8')) {
        //     $data = mb_convert_encoding($data, 'UTF-8');
        // }

        if(empty($data)) return;

        SocketMemory::$send[] = [
            'data' => $this->seal($data),
            'id' => $client_id
        ];
    }

    public function close(int $client_id)
    {
        if (!array_key_exists($client_id, SocketMemory::$clients)) return false;
        if (in_array($client_id, SocketMemory::$close)) return true;

        SocketMemory::$close[] = $client_id;

        return true;
    }

    public function setProps(int $client_id, mixed $value)
    {
        if (!array_key_exists($client_id, SocketMemory::$clients)) return false;

        SocketMemory::$props[$client_id] = $value;

        return true;
    }

    public function getProps(int $client_id)
    {
        if (!array_key_exists($client_id, SocketMemory::$props)) return null;

        return SocketMemory::$props[$client_id];
    }

    public function clearProps(int $client_id)
    {
        if (!array_key_exists($client_id, SocketMemory::$props)) return false;

        unset(SocketMemory::$props[$client_id]);

        return true;
    }

    /**
     * @return int|null
     */
    public function id()
    {
        if (!SocketMemory::$client_id) return null;

        return SocketMemory::$client_id;
    }

    /**
     * @return array
     */
    public function ids(bool $this_client_id = true)
    {
        if (!count(SocketMemory::$clients)) return [];

        $ids = array_keys(SocketMemory::$clients);

        if (!$this_client_id) {
            $not_this_id = array_search(SocketMemory::$client_id, $ids);

            if ($not_this_id) unset($ids[$not_this_id]);
        };

        if (array_key_exists(0, $ids)) unset($ids[0]);

        return $ids;
    }

    /**
     * @return string|null
     */
    public function ip(int $client_id)
    {
        if (!array_key_exists($client_id, SocketMemory::$ips)) return null;

        return SocketMemory::$ips[$client_id];
    }

    /**
     * @return int
     */
    public function count()
    {
        return count(SocketMemory::$clients) - 1;
    }

    /**
     * @return string|array|null
     */
    public function header(int $client_id, string|null $key = null)
    {
        if (!array_key_exists($client_id, SocketMemory::$headers)) return null;

        if ($key === null) return SocketMemory::$headers[$client_id];

        if (array_key_exists($key, SocketMemory::$headers[$client_id])) return SocketMemory::$headers[$client_id][$key];
    }

    private function seal($socketData)
    {
        $b1 = 0x80 | (0x1 & 0x0f);
        $length = strlen($socketData);

        if ($length <= 125) {
            $header = pack('CC', $b1, $length);
        } elseif ($length < 65536) {
            $header = pack('CCn', $b1, 126, $length);
        } else {
            $header = pack('CCNN', $b1, 127, 0, $length);
        }

        return $header . $socketData;
    }
}
