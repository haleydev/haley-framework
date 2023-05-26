<?php
namespace Haley\Collections;

class Password
{
    public string $salt = '';

    /**
     * Retorna um hash de uma string
     * @param string $password
     * @return string|false 
     */
    public function create(string $password){
        $rash = password_hash($this->salt.$password, PASSWORD_DEFAULT);
        return $rash;
    }

    /**
     * Verifica se o password bate com o hash, retorna true ou false
     * @param string $password
     * @param string $hash
     * @return true|false 
     */
    public function check(string $password, string $hash){
        return password_verify($this->salt.$password, $hash);
    }

    /**
     * Cria um token random
     * @return string 
     */
    public function token(int $length = 5)
    {
        return strtoupper(bin2hex(random_bytes($length)));
    }
}