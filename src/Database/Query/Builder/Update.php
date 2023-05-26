<?php
namespace Haley\Database\Query\Builder;
use Core\Database\Query\Syntaxes\Syntax;

class Update extends Syntax
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
     * Add a "table" clause to the query
     */
    public function table(string $table, null|string $as = null)
    {
        $this->add('table', [           
            'table' => $table,
            'as' => $as          
        ],false);

        return $this;
    }

    public function setValues(array $values)
    {
        $this->add('set', [            
            'values' => $values
        ]);

        return $this;
    }  

    /**
     * Add a basic "where" clause to the query
     */
    public function where(string $column, null|string $operator = null, null|string $value = null, string $boolean = 'AND')
    {
        if (func_num_args() === 2 and $value == null) {
            $this->add('where', [
                'type' => 'where',
                'column' => $column,
                'operator' => '=',
                'value' => $operator,
                'boolean' => $boolean
            ]);
        } else {
            $this->add('where', [
                'type' => 'where',
                'column' => $column,
                'operator' => $operator,
                'value' => $value,
                'boolean' => $boolean
            ]);
        }

        return $this;
    }

    /**
     * Add multiple "where" clause in query
     */
    public function whereCompact(array $compact, string $operator = '=', string $boolean = 'AND')
    {
        $this->add('where', [
            'type' => 'where_compact',
            'values' => $compact,
            'operator' => $operator,
            'boolean' => $boolean
        ]);

        return $this;
    }

    /**
     * Add a raw "where" clause to the query
     */
    public function whereRaw(string $raw, array $bindparams = [], string $boolean = 'AND')
    {
        $this->add('where', [
            'type' => 'where_raw',
            'operator' => false,
            'raw' => $raw,
            'boolean' => $boolean,
            'bindparams' => $bindparams
        ]);

        return $this;
    }

    /**
     * Add a "where not in" clause to the query
     */
    public function whereNotIn(string $column, array $values, string $boolean = 'AND')
    {
        $this->add('where', [
            'type' => 'where_not_in',
            'operator' => false,
            'values' => $values,
            'boolean' => $boolean,
            'column' => $column
        ]);

        return $this;
    }

    /**
     * Add a "where in" clause to the query
     */
    public function whereIn(string $column, array $values, string $boolean = 'AND')
    {
        $this->add('where', [
            'type' => 'where_in',
            'operator' => false,
            'values' => $values,
            'boolean' => $boolean,
            'column' => $column
        ]);

        return $this;
    }

    /**
     * Add a "where null" clause to the query
     */
    public function whereNull(string|array $column, string $boolean = 'AND')
    {
        if (is_string($column)) {
            $column = explode(',', $column);
        }

        $this->add('where', [
            'type' => 'where_null',
            'operator' => false,
            'boolean' => $boolean,
            'column' => $column
        ]);

        return $this;
    }

    /**
     * Add a "where not null" clause to the query
     */
    public function whereNotNull(string|array $column, string $boolean = 'AND')
    {
        if (is_string($column)) {
            $column = explode(',', $column);
        }

        $this->add('where', [
            'type' => 'where_not_null',
            'operator' => false,
            'boolean' => $boolean,
            'column' => $column
        ]);

        return $this;
    }

    /**
     * Add a "where year" statement to the query
     */
    public function whereYear(string $column, int $year, string $operator = '=', string $boolean = 'AND')
    {
        $this->add('where', [
            'type' => 'where_year',
            'operator' => $operator,
            'boolean' => $boolean,
            'column' => $column,
            'year' => $year
        ]);

        return $this;
    }

    /**
     * Add a "where month" statement to the query
     */
    public function whereMonth(string $column, int $month, string $operator = '=', string $boolean = 'AND')
    {
        $this->add('where', [
            'type' => 'where_month',
            'operator' => $operator,
            'boolean' => $boolean,
            'column' => $column,
            'month' => $month
        ]);

        return $this;
    }

    /**
     * Add a "where day" statement to the query
     */
    public function whereDay(string $column, int $day, string $operator = '=', string $boolean = 'AND')
    {
        $this->add('where', [
            'type' => 'where_day',
            'operator' => $operator,
            'boolean' => $boolean,
            'column' => $column,
            'day' => $day
        ]);

        return $this;
    }

    /**
     * Add a "where date" statement to the query
     */
    public function whereDate(string $column, string $date, string $operator = '=', string $boolean = 'AND')
    {
        $this->add('where', [
            'type' => 'where_date',
            'operator' => $operator,
            'boolean' => $boolean,
            'column' => $column,
            'date' => $date
        ]);

        return $this;
    }

    /**
     * Add a "where hour" statement to the query
     */
    public function whereHour(string $column, int $hour, string $operator = '=', string $boolean = 'AND')
    {
        $this->add('where', [
            'type' => 'where_hour',
            'operator' => $operator,
            'boolean' => $boolean,
            'column' => $column,
            'hour' => $hour
        ]);

        return $this;
    }

    /**
     * Add a "where minute" statement to the query
     */
    public function whereMinute(string $column, int $minute, string $operator = '=', string $boolean = 'AND')
    {
        $this->add('where', [
            'type' => 'where_minute',
            'operator' => $operator,
            'boolean' => $boolean,
            'column' => $column,
            'minute' => $minute
        ]);

        return $this;
    }

    /**
     * Add a "where second" statement to the query
     */
    public function whereSecond(string $column, int $second, string $operator = '=', string $boolean = 'AND')
    {
        $this->add('where', [
            'type' => 'where_second',
            'operator' => $operator,
            'boolean' => $boolean,
            'column' => $column,
            'second' => $second
        ]);

        return $this;
    }

    /**
     * Add a "where time" statement to the query
     * @param string $time 00:00:00
     */
    public function whereTime(string $column, string $time, string $operator = '=', string $boolean = 'AND')
    {
        $this->add('where', [
            'type' => 'where_time',
            'operator' => $operator,
            'boolean' => $boolean,
            'column' => $column,
            'time' => $time
        ]);

        return $this;
    }

    /**
     * Add a "where between" statement to the query
     */
    public function whereBetween(string $column, string $start, string $end, $boolean = 'AND')
    {
        $this->add('where', [
            'type' => 'where_between',
            'operator' => false,
            'boolean' => $boolean,
            'column' => $column,
            'start' => $start,
            'end' => $end
        ]);

        return $this;
    }

    /**
     * Add a "where not between" statement to the query
     */
    public function whereNotBetween(string $column, string $start, string $end, $boolean = 'AND')
    {
        $this->add('where', [
            'type' => 'where_not_between',
            'operator' => false,
            'boolean' => $boolean,
            'column' => $column,
            'start' => $start,
            'end' => $end
        ]);

        return $this;
    } 

    /**
     * Add a "limit" clause to the query
     */
    public function limit(int $limit)
    {
        $this->add('limit', [
            'limit' => $limit,
            'page' => null
        ], false);

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
        $this->executeSyntax('update',$config['driver']);

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
        $this->executeSyntax('update',$config['driver']);

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
        $this->executeSyntax('update',$config['driver']);

        return $this->bindparams;
    }
    
    /**
     * Execute the update query   
     * @return int rowCount
     */
    public function execute()
    {   
        $execute = new Execute;
        $config = $execute->config($this->connection);  
        $this->executeSyntax('update',$config['driver']);

        return $execute->update($this->query,$this->bindparams,$this->connection);
    }     
}
