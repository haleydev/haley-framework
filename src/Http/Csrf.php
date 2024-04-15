<?php

namespace Haley\Http;

class Csrf
{
    public static function unset()
    {
        request()->session()->replace('HALEY', ['csrf' => null]);
    }

    public static function token()
    {
        $check = request()->session('HALEY');

        if (!empty($check['csrf']['lifetime']) and $check['csrf']['lifetime'] > date('dmYHis')) {
            return $check['csrf']['token'];
        }

        $token = md5(bin2hex(random_bytes(10)));
        $lifetime = 1800;

        if (defined('ROUTER_NOW')) $lifetime = ROUTER_NOW['config']['csrf']['lifetime'] ?? 1800;

        request()->session()->replace('HALEY', ['csrf' => [
            'token' => $token,
            'lifetime' => date('dmYHis', strtotime('+' . $lifetime . ' seconds'))
        ]]);

        return $token;
    }

    public static function check()
    {
        $token = self::token();

        $header_token = request()->headers('X-CSRF-TOKEN') ?: request()->headers('X-Csrf-Token');
        $input_token = request()->post('_token');

        if ($token == $header_token or $token == $input_token) return true;

        return false;
    }
}
