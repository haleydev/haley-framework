<?php

namespace Haley\Console;

class Lines
{
    public static function red(string $value)
    {
        echo "\033[0;31m$value \033[0m";
        return new self;
    }

    public static function green(string $value)
    {
        echo "\033[0;32m$value \033[0m";
        return new self;
    }

    public static function yellow(string $value)
    {
        echo "\e[93m$value \033[0m";
        return new self;
    }

    public static function normal(string $value)
    {
        echo $value . ' ';
        return new self;
    }

    public static function blue(string $value)
    {
        echo "\033[0;34m$value \033[0m";
        return new self;
    }

    public static function gray(string $value)
    {
        echo "\e[90m$value \033[0m";
        return new self;
    }

    public static function br()
    {
        echo PHP_EOL;

        return new self;
    }

    /**
     * @return string
     */
    public static function readline()
    {
        return readline('') ?? '';
    }

    // centralizar texto
    // function centralizarTexto($texto, $larguraTerminal) {
    //     $tamanhoTexto = strlen($texto);
    //     $espacosAntes = ($larguraTerminal - $tamanhoTexto) / 2;
    //     $espacosAntes = floor($espacosAntes); // Arredondar para baixo para garantir que seja um número inteiro

    //     // Adicionar espaços antes do texto
    //     echo str_repeat(" ", $espacosAntes) . $texto . PHP_EOL;
    // }

    // // Exemplo de uso
    // $texto = "Texto centralizado";
    // $larguraTerminal = exec('tput cols'); // Obtém a largura do terminal
    // centralizarTexto($texto, $larguraTerminal);
}