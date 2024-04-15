<?php

namespace Haley\Collections;

class Password
{
    public static string $salt = '';

    /**
     * Retorna um hash de uma string
     * @param string $password
     * @return string|false
     */
    public static function create(string $password)
    {
        $rash = password_hash(self::$salt . $password, PASSWORD_DEFAULT);
        return $rash;
    }

    /**
     * Verifica se o password bate com o hash, retorna true ou false
     * @param string $password
     * @param string $hash
     * @return true|false
     */
    public static function check(string $password, string $hash)
    {
        return password_verify(self::$salt . $password, $hash);
    }

    /**
     * Cria um token random
     * @return string
     */
    public static function token(int $length = 5)
    {
        return strtoupper(bin2hex(random_bytes($length)));
    }
}
