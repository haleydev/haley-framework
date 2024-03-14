<?php

namespace Haley\WebSocket;

class SocketController
{
    public function send(mixed $data, int|array|null $id = null)
    {
        if (is_int($id)) $id = [$id];
        elseif (is_null($id)) $id = $this->ids();

        SocketMemory::$send[] = [
            'data' => $this->seal(json_encode($data)),
            'id' => $id
        ];
    }

    public function close(int $id)
    {
        // if (array_key_exists($id, SocketMemory::$props)) unset(SocketMemory::$props[$id]);
        // if (array_key_exists($id, SocketMemory::$ips)) unset(SocketMemory::$ips[$id]);

        if (!array_key_exists($id, SocketMemory::$clients)) return false;
        if (in_array($id, SocketMemory::$close)) return true;

        SocketMemory::$close[] = $id;

        return true;
    }

    public function setProps(int $id, mixed $value)
    {
        if (!array_key_exists($id, SocketMemory::$clients) || $id === null) return false;

        SocketMemory::$props[$id] = $value;

        return true;
    }

    public function getProps(int $id)
    {
        if (!array_key_exists($id, SocketMemory::$props)) return null;

        return SocketMemory::$props[$id];
    }

    /**
     * @return int|null
     */
    public function id()
    {
        if (SocketMemory::$id == 0) return null;

        return SocketMemory::$id;
    }

    /**
     * @return array
     */
    public function ids()
    {
        if (!count(SocketMemory::$clients)) return [];

        $ids = array_keys(SocketMemory::$clients);

        if (array_key_exists(0, $ids)) unset($ids[0]);

        return $ids;
    }

    public function ip(int $id)
    {
        if (!array_key_exists($id, SocketMemory::$ips)) return null;

        return SocketMemory::$ips[$id];
    }

    public function count()
    {
        return count(SocketMemory::$clients) - 1;
    }

    private function seal($value)
    {
        $b1 = 0x80 | (0x1 & 0x0f);
        $length = strlen($value);

        if ($length <= 125) $header = pack('CC', $b1, $length);
        elseif ($length > 125 && $length < 65536) $header = pack('CCn', $b1, 126, $length);
        elseif ($length >= 65536) $header = pack('CCNN', $b1, 127, $length);

        return $header . $value;
    }
}
