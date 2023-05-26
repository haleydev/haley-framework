<?php
namespace Core\Console;

class Lines
{
    public function red(string $mesage, bool $eol = true) 
    {
        $line = $eol ? PHP_EOL : '';     

        echo "\033[0;31m$mesage\033[0m $line";
        return;
    }

    public function green(string $mesage, bool $eol = true) 
    {
        $line = $eol ? PHP_EOL : '';     

        echo "\033[0;32m$mesage\033[0m $line";
        return;
    }

    public function yellow(string $mesage, bool $eol = true) 
    {
        $line = $eol ? PHP_EOL : '';     

        echo "\033[0;43m$mesage\033[0m $line";
        return;
    }

    public function white(string $mesage, bool $eol = true) 
    {
        $line = $eol ? PHP_EOL : '';     

        echo "$mesage $line";
        return;
    }

    public function blue(string $mesage, bool $eol = true)
    {
        $line = $eol ? PHP_EOL : '';     

        echo "\033[0;34m$mesage\033[0m $line";
        return;
        
    }

    // php -r 'echo "\033[31m some colored text \033[0m some white text \n";'

    /**
     * @return string
     */
    public function readline() {
        return (string)readline('');
    }   
}