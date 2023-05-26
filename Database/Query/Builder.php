<?php
namespace Core\Database\Query;

use Core\Database\Query\Builder\Execute;
use Core\Database\Query\Syntaxes\Syntax;
use InvalidArgumentException;

class Builder extends Syntax
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
     * Add a "from" clause to the query
     */
    public function table(string $table, null|string $as = null)
    {
        $this->add('table', [
            'table' => $table,
            'as' => $as
        ], false);

        return $this;
    }

    /**
     * Add a raw "from" clause to the query
     */
    public function fromRaw(string $raw, array $bindparams = [])
    {
        $this->add('table_raw', [
            'raw' => $raw,
            'bindparams' => $bindparams
        ]);

        return $this;
    }

    /**
     * Add columns to the query
     */
    public function select(string|array ...$columns)
    {
        if (count($columns) == 0) {
            throw new InvalidArgumentException('Undefined variable $columns');
        }

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
     * Add a raw column clause to the query
     */
    public function selectRaw(string $raw, array $bindparams = [])
    {
        $this->add('columns', [
            'type' => 'column_raw',
            'raw' => $raw,
            'bindparams' => $bindparams
        ]);

        return $this;
    }

    /**
     * Retrieve the "count" result of the query
     */
    public function selectCount(string $column, null|string $as = null)
    {
        $this->add('columns', [
            'type' => 'column_count',
            'column' => $column,
            'as' => $as
        ]);

        return $this;
    }

    /**
     * Retrieve the average of the values of a given column
     */
    public function selectAvg(string $column, null|string $as = null)
    {
        $this->add('columns', [
            'type' => 'column_avg',
            'column' => $column,
            'as' => $as
        ]);

        return $this;
    }

    /**
     * Retrieve the sum of the values of a given column
     */
    public function selectSum(string $column, null|string $as = null)
    {
        $this->add('columns', [
            'type' => 'column_sum',
            'column' => $column,
            'as' => $as
        ]);

        return $this;
    }

    /**
     * Retrieve the minimum value of a given column
     */
    public function selectMin(string $column, null|string $as = null)
    {
        $this->add('columns', [
            'type' => 'column_min',
            'column' => $column,
            'as' => $as
        ]);

        return $this;
    }

    /**
     * Retrieve the maximum value of a given column
     */
    public function selectMax(string $column, null|string $as = null)
    {
        $this->add('columns', [
            'type' => 'column_max',
            'column' => $column,
            'as' => $as
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
     * Add a basic "or where" clause to the query
     */
    public function orWhere(string $column, null|string $operator = null, null|string $value = null)
    {
        $this->where($column, $operator, $value, 'OR');
    }

    /**
     * Add multiple "where" clause in query
     */
    public function whereCompact(callable $wheres, ...$use)
    {
        if (is_callable($wheres)) {
            if (isset($this->params['where'])) {
                $array_key = array_key_last($this->params['where']) + 1;
            } else {
                $array_key = 0;
            }

            call_user_func($wheres, $use);

            if (isset($this->params['where']) and isset($this->params['where'][$array_key])) {
                $this->params['where'][$array_key]['compact'] = 'start';

                $array_key = array_key_last($this->params['where']);
                $this->params['where'][$array_key]['compact'] = 'end';
            }
        }

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
     * Add a "join" clause to the query
     */
    public function join(string $table, string $first, string $second, $operator = '=')
    {
        $this->add('join', [
            'type' => 'join',
            'table' => $table,
            'first' => $first,
            'second' => $second,
            'operator' => $operator
        ]);

        return $this;
    }

    /**
     * Add a "left join" clause to the query
     */
    public function leftJoin(string $table, string $first, string $second, $operator = '=')
    {
        $this->add('join', [
            'type' => 'left_join',
            'table' => $table,
            'first' => $first,
            'second' => $second,
            'operator' => $operator
        ]);

        return $this;
    }

    /**
     * Add a "right join" clause to the query
     */
    public function rightJoin(string $table, string $first, string $second, string $operator = '=')
    {
        $this->add('join', [
            'type' => 'right_join',
            'table' => $table,
            'first' => $first,
            'second' => $second,
            'operator' => $operator
        ]);

        return $this;
    }

    /**
     * Add a "cross join" clause to the query
     */
    public function crossJoin(string $table)
    {
        $this->add('join', [
            'type' => 'cross_join',
            'table' => $table,
            'operator' => false
        ]);

        return $this;
    }

    /**
     * Add a "having" clause to the query
     */
    public function having(string $having, array $bindparams = [])
    {
        $this->add('having', [
            'having' => $having,
            'bindparams' => $bindparams
        ], false);

        return $this;
    }

    /**
     * Add a "group by" clause to the query
     */
    public function groupBy(string ...$column)
    {
        if (count($column) == 0) {
            throw new InvalidArgumentException('Undefined variable $column');
        }

        $this->add('group', [
            'columns' => $column
        ], false);

        return $this;
    }

    /**
     * Add a "distinct" clause to the query
     */
    public function distinct()
    {
        $this->add('distinct', true, false);
        return $this;
    }

    /**
     * Add a "limit" clause to the query
     */
    public function limit(int $limit, int|null $page = null)
    {
        $this->add('limit', [
            'limit' => $limit,
            'page' => $page
        ], false);

        return $this;
    }

    /**
     * Add a "order by desc" clause to the query
     */
    public function orderByDesc(string ...$column)
    {
        if (count($column) == 0) {
            throw new InvalidArgumentException('Undefined variable $column');
        }

        $this->add('order', [
            'type' => 'desc',
            'column' => $column
        ], false);

        return $this;
    }

    /**
     * Add a "order by asc" clause to the query
     */
    public function orderByAsc(string ...$column)
    {
        if (count($column) == 0) {
            throw new InvalidArgumentException('Undefined variable $column');
        }

        $this->add('order', [
            'type' => 'asc',
            'column' => $column
        ], false);

        return $this;
    }

    /**
     * Add a "order by rand()" clause to the query
     */
    public function orderByRand()
    {
        $this->add('order', [
            'type' => 'rand',
            'column' => false
        ], false);

        return $this;
    }

    /**
     * Add a raw "order by" clause to the query
     */
    public function orderByRaw(string $raw, array $bindparams = [])
    {
        $this->add('order', [
            'type' => 'raw',
            'raw' => $raw,
            'bindparams' => $bindparams
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
     * @var string $type select|delete|update|insert
     * @return array
     */
    public function getParams(string $type = 'select')
    {
        $execute = new Execute;
        $config = $execute->config($this->connection);
        $this->executeSyntax($type, $config['driver']);

        return $this->params;
    }

    /**
     * Returns the assembled query
     * @var string $type select|delete|update|insert
     * @return string
     */
    public function getQuery(string $type = 'select')
    {
        $execute = new Execute;
        $config = $execute->config($this->connection);
        $this->executeSyntax($type, $config['driver']);

        return $this->query;
    }

    /**
     * Returns all bindparams passed in the query
     * @var string $type select|delete|update|insert
     * @return array
     */
    public function getBindparams(string $type = 'select')
    {
        $execute = new Execute;
        $config = $execute->config($this->connection);
        $this->executeSyntax($type, $config['driver']);

        return $this->bindparams;
    }

    /**
     * Execute the select query
     * @return array|object 'fetch'
     */
    public function one()
    {
        $execute = new Execute;
        $config = $execute->config($this->connection);
        $this->executeSyntax('select', $config['driver']);

        return $execute->select($this->query, $this->bindparams, $this->connection, false);
    }

    /**
     * Execute the select query
     * @return array|object 'fetchAll'
     */
    public function all()
    {
        $execute = new Execute;
        $config = $execute->config($this->connection);
        $this->executeSyntax('select', $config['driver']);

        return $execute->select($this->query, $this->bindparams, $this->connection);
    }

    public function update(array $values)
    {
        $this->add('update', [
            'values' => $values
        ]);

        $execute = new Execute;
        $config = $execute->config($this->connection);
        $this->executeSyntax('update', $config['driver']);

        return $execute->update($this->query, $this->bindparams, $this->connection);
    }

    public function updateIgnore(array $values)
    {
        $this->add('ignore', true, false);

        $this->add('update', [
            'values' => $values
        ]);

        $execute = new Execute;
        $config = $execute->config($this->connection);
        $this->executeSyntax('update', $config['driver']);

        return $execute->update($this->query, $this->bindparams, $this->connection);
    }

    /**
     * Execute the insert query
     * @return int rowCount
     */
    public function insert(array $values)
    {
        $this->add('insert', [
            'type' => 'insert',
            'values' => $values
        ], false);

        $execute = new Execute;
        $config = $execute->config($this->connection);
        $this->executeSyntax('insert', $config['driver']);

        return $execute->insert($this->query, $this->bindparams, $this->connection);
    }

    /**
     * Execute the insert query
     * @return int|string|false lastInsertId
     */
    public function insertGetId(array $values)
    {
        $this->add('insert', [
            'type' => 'insert',
            'values' => $values
        ], false);

        $execute = new Execute;
        $config = $execute->config($this->connection);
        $this->executeSyntax('insert', $config['driver']);

        return $execute->insert($this->query, $this->bindparams, $this->connection, true);
    }

    /**
     * Execute the insert query
     * @return int rowCount
     */
    public function insertIgnore(array $values)
    {
        $this->add('ignore', true, false);

        $this->add('insert', [
            'type' => 'insert',
            'values' => $values
        ], false);

        $execute = new Execute;
        $config = $execute->config($this->connection);
        $this->executeSyntax('insert', $config['driver']);

        return $execute->insert($this->query, $this->bindparams, $this->connection);
    }

    /**
     * Execute the insert query using
     * @return int|string|false lastInsertId
     */
    public function insertUsing(array $columns, string $query)
    {
        $this->add('insert', [
            'type' => 'insert_using',
            'columns' => $columns,
            'query' => $query
        ], false);

        $execute = new Execute;
        $config = $execute->config($this->connection);
        $this->executeSyntax('insert', $config['driver']);

        return $execute->insert($this->query, $this->bindparams, $this->connection);
    }

    /**
     * Execute the delete query 
     * @return int 'rowCount'
     */
    public function delete()
    {
        $execute = new Execute;
        $config = $execute->config($this->connection);
        $this->executeSyntax('delete', $config['driver']);

        return $execute->delete($this->query, $this->bindparams, $this->connection);
    }

    /**
     * @return array|object
     */
    public function explain()
    {
        $this->add('explain', true, false);

        $execute = new Execute;
        $config = $execute->config($this->connection);
        $this->executeSyntax('select', $config['driver']);

        return $execute->select($this->query, $this->bindparams, $this->connection);
    }

    /**
     * Execute the select query
     * @return int 'rowCount'
     */
    public function count()
    {
        $execute = new Execute;
        $config = $execute->config($this->connection);
        $this->executeSyntax('select', $config['driver']);

        return $execute->select($this->query, $this->bindparams, $this->connection, count: true);
    }
}
