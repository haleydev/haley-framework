<?php
namespace Haley\Database\Query\Builder;
use Core\Database\Query\Syntaxes\Syntax;

class Insert extends Syntax
{
    /**
     * Standard env connection
     */
    protected string $connection = 'default';

    /**
     * Use a specific connection
     */
    public function connection(string $connection = 'default')
    {
        $this->connection = $connection;
        return $this;
    }    

    /**
     * Add a "into" clause to the query
     */
    public function into(string $table)
    {
        $this->add('into',$table,false);

        return $this;
    }

    /**
     * Add a raw "into" clause to the query
     */
    public function intoRaw(string $raw, array $bindparams = [])
    {
        $this->add('into_raw', [           
            'raw' => $raw,
            'bindparams' => $bindparams         
        ]);

        return $this;
    }   

    /**
     * Add a columns and values clause to the query
     */
    public function compact(array $compact)
    {
        $this->add('values', [
            'type' => 'compact',
            'values' => $compact
        ],false);

        return $this;
    }

    /**
     * Add a values clause to the query
     */
    public function values(string|array ...$values)
    {
        if (is_array($values[0])) {
            $values = $values[0];
        }

        $this->add('values', [
            'type' => 'values',
            'values' => $values
        ]);

        return $this;
    }

    /**
     * Add a columns clause to the query
     */
    public function columns(string|array ...$columns)
    {
        if (is_array($columns[0])) {
            $columns = $columns[0];
        }

        $this->add('columns', [
            'type' => 'column',
            'column' => $columns
        ]);

        return $this;
    }

    /**
     * Add ignore clause to the query
     */
    public function ignore()
    {
        $this->add('ignore', true, false);
        return $this;
    }

    /**
     * Add raw to the end of the query
     */
    public function raw(string $raw, array $bindparams = [])
    {
        $this->add('raw', [
            'raw' => $raw,
            'bindparams' => $bindparams           
        ]);

        return $this;
    }

    /**
     * Returns all parameters passed in the query
     * @return array
     */
    public function getParams()
    {      
        $execute = new Execute;
        $config = $execute->config($this->connection); 
        $this->executeSyntax('insert',$config['driver']);

        return $this->params;
    }

    /**
     * Returns the assembled query
     * @return string
     */
    public function getQuery()
    {       
        $execute = new Execute;
        $config = $execute->config($this->connection); 
        $this->executeSyntax('insert',$config['driver']);

        return $this->query;
    }

    /**
     * Returns all bindparams passed in the query
     * @return array
     */
    public function getBindparams()
    { 
        $execute = new Execute;
        $config = $execute->config($this->connection); 
        $this->executeSyntax('insert',$config['driver']);

        return $this->bindparams;
    }

    /**
     * Execute the insert query
     * @return int rowCount
     */
    public function execute()
    {  
        $execute = new Execute;
        $config = $execute->config($this->connection);
        $this->executeSyntax('insert',$config['driver']);

        return $execute->insert($this->query,$this->bindparams,$this->connection);
    }    
    
    /**
     * Execute the insert query
     * @return int|string|false lastInsertId
     */
    public function executeGetId()
    {  
        $execute = new Execute;
        $config = $execute->config($this->connection);
        $this->executeSyntax('insert',$config['driver']);

        return $execute->insert($this->query,$this->bindparams,$this->connection,true);
    }    
}