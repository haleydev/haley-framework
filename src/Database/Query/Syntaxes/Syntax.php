<?php
namespace Core\Database\Query\Syntaxes;
use Core\Collections\Log;
use InvalidArgumentException;

class Syntax
{ 
    protected string $query;   
    protected array $bindparams = [];
    protected array $params = [];

    protected function add(string $action, $params, bool $array = true)
    {
        if ($array == true) {
            $this->params[$action][] = $params;
        } else {
            $this->params[$action] = $params;
        }
    }

    protected function executeSyntax(string $command,string $driver)
    {  
        if ($driver == 'mysql') {
            $syntax = new Mysql;
            $syntax->params = $this->params;          
        } 
        
        elseif ($driver == 'sql') {
            // ...
        }
        
        else {
            Log::create('connection', "Drive not found! ( {$driver} )");   
            throw new InvalidArgumentException("Drive not found! ( {$driver} )");
        }
      
        $this->query = $syntax->query($command);   
        $this->bindparams = $syntax->bindparams;       
    }
}
