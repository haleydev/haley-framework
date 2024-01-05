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

    public function setProps(int $id, mixed $value)
    {
        if (!array_key_exists($id, SocketMemory::$clients)) return false;

        SocketMemory::$props[$id] = $value;

        return true;
    }

    public function getProps(int $id)
    {
        if (array_key_exists($id, SocketMemory::$props)) return null;

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

    private function seal($value)
    {
        $b1 = 0x80 | (0x1 & 0x0f);
        $length = strlen($value);

        if ($length <= 125) $header = pack('CC', $b1, $length);
        elseif ($length > 125 && $length < 65536) $header = pack('CCn', $b1, 126, $length);
        elseif ($length >= 65536) $header = pack('CCNN', $b1, 127, $length);

        return $header . $value;
    }

    private function  unseal($value)
    {
        $length = ord($value[1]) & 127;

        if ($length == 126) {
            $masks = substr($value, 4, 4);
            $data = substr($value, 8);
        } elseif ($length == 127) {
            $masks = substr($value, 10, 4);
            $data = substr($value, 14);
        } else {
            $masks = substr($value, 2, 4);
            $data = substr($value, 6);
        }

        $value = '';

        for ($i = 0; $i < strlen($data); ++$i) $value .= $data[$i] ^ $masks[$i % 4];

        return $value;
    }
}
