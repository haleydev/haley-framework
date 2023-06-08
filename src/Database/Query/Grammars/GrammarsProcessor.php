<?php

namespace Haley\Database\Query\Grammars;

use InvalidArgumentException;

class GrammarsProcessor
{
    public array $params = [];
    public array $bindparams = [];

    private string|false $where_compact = false;
    private string $table = '';
    private string $table_raw = '';
    private string $columns = '';
    private string $where = '';
    private string $limit = '';
    private string $order = '';
    private string $having = '';
    private string $group = '';
    private string $join = '';
    private string $insert = '';
    private string $update = '';
    private string $raw = '';

    private array $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=', '<=>',
        'like', 'like binary', 'not like', 'ilike',
        '&', '|', '^', '<<', '>>', '&~', 'is', 'is not',
        'rlike', 'not rlike', 'regexp', 'not regexp',
        '~', '~*', '!~', '!~*', 'similar to',
        'not similar to', 'not ilike', '~~*', '!~~*'
    ];

    private function table(array $params)
    {
        if (!empty($params['as'])) {
            $table = "{$params['table']} AS {$params['as']}";
        } else {
            $table = "{$params['table']}";
        }

        $this->table = $this->quotes($table);
        return;
    }

    private function tableRaw(array $params)
    {
        $table_raw = '';

        foreach ($params as $value) {
            $table_raw = " {$value['raw']}";

            if (count($value['bindparams']) > 0) {
                foreach ($value['bindparams'] as $bind) {
                    $this->bindparams[] = $bind;
                }
            }
        }

        $this->table_raw = trim($table_raw);
        return;
    }

    private function columns(array $columns)
    {
        foreach ($columns as $values) {
            // DEFAULT
            if ($values['type'] == 'column') {
                foreach ($values['column'] as $column) {
                    $this->columns = "{$this->columns},{$this->quotes($column, limit: 3)}";
                }
            }

            // COLUMN RAW
            elseif ($values['type'] == 'column_raw') {
                $this->columns = "{$this->columns},{$values['raw']}";

                if (count($values['bindparams']) > 0) {
                    foreach ($values['bindparams'] as $bind) {
                        $this->bindparams[] = $bind;
                    }
                }
            }

            // COLUMN COUNT
            elseif ($values['type'] == 'column_count') {
                if ($values['as'] == null) {
                    $this->columns = "{$this->columns},COUNT({$this->quotes($values['column'])})";
                } else {
                    $this->columns = "{$this->columns},COUNT({$this->quotes($values['column'])}) AS {$this->quotes($values['as'])}";
                }
            }

            // COLUMN AVG
            elseif ($values['type'] == 'column_avg') {
                if ($values['as'] == null) {
                    $this->columns = "{$this->columns},AVG({$this->quotes($values['column'])})";
                } else {
                    $this->columns = "{$this->columns},AVG({$this->quotes($values['column'])}) AS {$this->quotes($values['as'])}";
                }
            }

            // COLUMN SUM
            elseif ($values['type'] == 'column_sum') {
                if ($values['as'] == null) {
                    $this->columns = "{$this->columns},SUM({$this->quotes($values['column'])})";
                } else {
                    $this->columns = "{$this->columns},SUM({$this->quotes($values['column'])}) AS {$this->quotes($values['as'])}";
                }
            }

            // COLUMN MIN
            elseif ($values['type'] == 'column_min') {
                if ($values['as'] == null) {
                    $this->columns = "{$this->columns},MIN({$this->quotes($values['column'])})";
                } else {
                    $this->columns = "{$this->columns},MIN({$this->quotes($values['column'])}) AS {$this->quotes($values['as'])}";
                }
            }

            // COLUMN MAX
            elseif ($values['type'] == 'column_max') {
                if ($values['as'] == null) {
                    $this->columns = "{$this->columns},MAX({$this->quotes($values['column'])})";
                } else {
                    $this->columns = "{$this->columns},MAX({$this->quotes($values['column'])}) AS {$this->quotes($values['as'])}";
                }
            }
        }

        $this->columns = trim($this->columns, ',');
        return;
    }

    private function where(array $params)
    {
        // dd($params);
        // die;

        foreach ($params as $param) {
            if (!in_array(strtolower($param['operator']), $this->operators) and $param['operator'] != false) {
                throw new InvalidArgumentException("Invalid operator ( {$param['operator']} )");
            }

            $type = $param['type'];
            $open = $param['tag_open'] ?? false;
            $close = $param['tag_close'] ?? false;

            // WHERE
            if ($type == 'where') {
                $this->addWhere("{$this->quotes($param['column'])} {$param['operator']} ?", $param['boolean'], $open, $close);
                $this->bindparams[] = $param['value'];
            }

            // WHERE BETWEEN
            elseif ($type == 'where_between') {
                $this->addWhere("{$this->quotes($param['column'])} BETWEEN ? AND ?", $param['boolean'], $open, $close);
                $this->bindparams[] = $param['start'];
                $this->bindparams[] = $param['end'];
            }

            // WHERE NOT BETWEEN
            elseif ($type == 'where_not_between') {
                $this->addWhere("{$this->quotes($param['column'])} NOT BETWEEN ? AND ?", $param['boolean'], $open, $close);
                $this->bindparams[] = $param['start'];
                $this->bindparams[] = $param['end'];
            }

            // WHERE RAW
            elseif ($type == 'where_raw') {
                if (count($param['bindparams']) > 0) {
                    foreach ($param['bindparams'] as $value) {
                        $this->bindparams[] = $value;
                    }
                }

                $this->addWhere($param['raw'], $param['boolean'], $open, $close);
            }

            // WHERE NOT IN
            elseif ($type == 'where_not_in') {
                $binds = '';
                foreach ($param['values'] as $value) {
                    $this->bindparams[] = $value;
                    $binds = "$binds,?";
                }

                $binds = trim($binds, ",");
                $this->addWhere("{$this->quotes($param['column'])} NOT IN ($binds)", $param['boolean'], $open, $close);
            }

            // WHERE IN
            elseif ($type == 'where_in') {
                $binds = '';
                foreach ($param['values'] as $value) {
                    $this->bindparams[] = $value;
                    $binds = "$binds,?";
                }

                $binds = trim($binds, ",");
                $this->addWhere("{$this->quotes($param['column'])} IN ($binds)", $param['boolean'], $open, $close);
            }

            // WHERE NULL
            elseif ($type == 'where_null') {
                foreach ($param['columns'] as $column) {
                    $this->addWhere("{$this->quotes($column)} IS NULL", $param['boolean'], $open, $close);
                }
            }

            // WHERE NOT NULL
            elseif ($type == 'where_not_null') {
                foreach ($param['columns'] as $column) {
                    $this->addWhere("{$this->quotes($column)} IS NOT NULL", $param['boolean'], $open, $close);
                }
            }

            // WHERE YEAR
            elseif ($type == 'where_year') {
                $this->addWhere("YEAR({$this->quotes($param['column'])}) {$param['operator']} ?", $param['boolean'], $open, $close);
                $this->bindparams[] = $param['year'];
            }

            // WHERE MONTH
            elseif ($type == 'where_month') {
                $this->addWhere("MONTH({$this->quotes($param['column'])}) {$param['operator']} ?", $param['boolean'], $open, $close);
                $this->bindparams[] = $param['month'];
            }

            // WHERE DAY
            elseif ($type == 'where_day') {
                $this->addWhere("DAY({$this->quotes($param['column'])}) {$param['operator']} ?", $param['boolean'], $open, $close);
                $this->bindparams[] = $param['day'];
            }

            // WHERE DATE
            elseif ($type == 'where_date') {
                $this->addWhere("DATE({$this->quotes($param['column'])}) {$param['operator']} ?", $param['boolean'], $open, $close);

                $date = date("Y-m-d", strtotime($param['date']));
                $this->bindparams[] = $date;
            }

            // WHERE HOUR
            elseif ($type == 'where_hour') {
                $this->addWhere("HOUR({$this->quotes($param['column'])}) {$param['operator']} ?", $param['boolean'], $open, $close);

                $this->bindparams[] = $param['hour'];
            }

            // WHERE MINUTE
            elseif ($type == 'where_minute') {
                $this->addWhere("MINUTE({$this->quotes($param['column'])}) {$param['operator']} ?", $param['boolean'], $open, $close);

                $this->bindparams[] = $param['minute'];
            }

            // WHERE SECOND
            elseif ($type == 'where_second') {
                $this->addWhere("SECOND({$this->quotes($param['column'])}) {$param['operator']} ?", $param['boolean'], $open, $close);

                $this->bindparams[] = $param['second'];
            }

            // WHERE TIME
            elseif ($type == 'where_time') {
                $this->addWhere("TIME({$this->quotes($param['column'])}) {$param['operator']} ?", $param['boolean'], $open, $close);

                $this->bindparams[] = $param['time'];
            }
        }

        return;
    }

    private function addWhere(string $where, string $boolean, bool $open = false, bool $close = false)
    {
        $open = $open ? '(' : '';
        $close = $close ? ')' : '';

        if ($this->where == '') {
            $this->where = 'WHERE ' . $open . $where . $close;
        } else {
            $this->where = $this->where . ' ' . $boolean . ' ' . $open . $where . $close;
        }

        return;
    }

    private function join(array $params)
    {
        foreach ($params as $join) {
            if (!in_array(strtolower($join['operator']), $this->operators) and $join['operator'] != false) {
                throw new InvalidArgumentException("Invalid operator ( {$join['operator']} )");
            }

            // JOIN
            if ($join['type'] == 'join') {
                $this->addJoin("INNER JOIN {$this->quotes($join['table'])} ON {$this->quotes($join['first'])} {$join['operator']} {$this->quotes($join['second'])}");
            }

            // LEFT JOIN
            elseif ($join['type'] == 'left_join') {
                $this->addJoin("LEFT JOIN {$this->quotes($join['table'])} ON {$this->quotes($join['first'])} {$join['operator']} {$this->quotes($join['second'])}");
            }

            // RIGHT JOIN            
            elseif ($join['type'] == 'right_join') {
                $this->addJoin("RIGHT JOIN {$this->quotes($join['table'])} ON {$this->quotes($join['first'])} {$join['operator']} {$this->quotes($join['second'])}");
            }

            // CROSS JOIN
            elseif ($join['type'] == 'cross_join') {
                $this->addJoin("CROSS JOIN {$this->quotes($join['table'])}");
            }
        }
    }

    private function addJoin(string $join)
    {
        if ($this->join == '') {
            $this->join = "$join";
        } else {
            $this->join = "{$this->join} $join";
        }

        return;
    }

    private function having(array $params)
    {
        $this->having = $params['having'];

        if (count($params['bindparams']) > 0) {
            foreach ($params['bindparams'] as $value) {
                $this->bindparams[] = $value;
            }
        }

        return;
    }

    private function limit(array $params)
    {
        $page = $params['page'];
        $limit = $params['limit'];

        if ($page == null) {
            $this->limit = "LIMIT $limit";
        } else {
            $page = ($page - 1) * $limit;
            $this->limit = "LIMIT $page,$limit";
        }

        return;
    }

    private function order(array $params)
    {
        $type = $params['type'];

        if ($type == 'rand') {
            $this->order = 'ORDER BY RAND()';
        } elseif ($type == 'raw') {
            $this->order = "ORDER BY {$params['raw']}";

            if (count($params['bindparams'])  > 0) {
                foreach ($params['bindparams'] as $value) {
                    $this->bindparams[] = $value;
                }
            }
        } else {
            $order_by = '';
            foreach ($params['column'] as $column) {
                $order_by = "$order_by,{$this->quotes($column)}";
            }

            if ($type == 'desc') {
                $order = 'DESC';
            } elseif ($type == 'asc') {
                $order = 'ASC';
            }

            $columns = trim($order_by, ',');
            $this->order = "ORDER BY $columns $order";
        }

        return;
    }

    private function groupBy(array $params)
    {
        $group = 'GROUP BY';
        foreach ($params['columns'] as $column) {
            $group = "$group {$this->quotes($column)},";
        }

        $group = trim($group, ',');
        $this->group = $group;

        return;
    }

    private function insert(array $params)
    {
        if ($params['type'] == 'insert') {
            if (empty($params['values'])) {
                $this->insert = '';
                return;
            }

            $values = '';
            $columns = '';

            if (!is_array(reset($params['values']))) {
                $params['values'] = [$params['values']];
            }

            foreach ($params['values'] as $array) {

                $array_values = [];
                $array_columns = [];

                foreach ($array as $key => $value) {
                    if (!in_array($key, $array_columns) and !is_numeric($key)) {
                        $array_columns[] = $this->quotes($key);
                    }

                    $array_values[] = '?';
                    $this->bindparams[] = $value;
                }

                $values .=  !empty($array_values) ? ',(' . implode(',', $array_values) . ')' : '';
            }

            $values = trim($values, ',');
            $columns = !empty($array_columns) ? '(' . implode(',', $array_columns) . ')' : '';

            $this->insert =  $columns . ' VALUES ' . $values;
        }

        // Copy values other table
        elseif ($params['type'] == 'insert_using') {
            $columns = '';

            foreach ($params['columns'] as $column) {
                $columns = "{$columns},{$this->quotes($column)}";
            }

            $this->insert = '(' . trim($columns, ',') . ') ' .  $params['query'];
        }

        return;
    }

    private function update(array $params)
    {
        foreach ($params as $param) {
            foreach ($param['values'] as $column => $value) {
                $this->update = "{$this->update},{$this->quotes($column)} = ?";
                $this->bindparams[] = $value;
            }
        }

        $this->update = 'SET ' . trim($this->update, ',');
        return;
    }

    private function raw(array $params)
    {
        foreach ($params as $value) {
            $this->raw = "{$this->raw} {$value['raw']}";

            if (count($value['bindparams']) > 0) {
                foreach ($value['bindparams'] as $bind) {
                    $this->bindparams[] = $bind;
                }
            }
        }

        $this->raw = trim($this->raw);

        return;
    }

    public function query(string $command, string $driver)
    {
        isset($this->params['explain']) ?  $explain = 'EXPLAIN' : $explain = '';
        isset($this->params['distinct']) ? $distinct = 'DISTINCT' : $distinct = '';
        isset($this->params['ignore']) ?  $ignore = 'IGNORE' : $ignore = '';

        if (isset($this->params['columns'])) {
            $this->columns($this->params['columns']);
        } else {
            $this->columns = '*';
        }

        if (isset($this->params['insert'])) {
            $this->insert($this->params['insert']);
        }

        if (isset($this->params['table'])) {
            $this->table($this->params['table']);
        }

        if (isset($this->params['table_raw'])) {
            $this->tableRaw($this->params['table_raw']);
        }

        if (isset($this->params['update'])) {
            $this->update($this->params['update']);
        }

        if (isset($this->params['join'])) {
            $this->join($this->params['join']);
        }

        if (isset($this->params['where'])) {
            $this->where($this->params['where']);
        }

        if (isset($this->params['group'])) {
            $this->groupBy($this->params['group']);
        }

        if (isset($this->params['having'])) {
            $this->having($this->params['having']);
        }

        if (isset($this->params['raw'])) {
            $this->raw($this->params['raw']);
        }

        if (isset($this->params['order'])) {
            $this->order($this->params['order']);
        }

        if (isset($this->params['limit'])) {
            $this->limit($this->params['limit']);
        }

        $table = $this->table;
        $table_raw = $this->table_raw;
        $columns = $this->columns;

        $where = $this->where;
        $limit = $this->limit;
        $order = $this->order;
        $having = $this->having;
        $group = $this->group;
        $join = $this->join;
        $raw = $this->raw;

        $insert = $this->insert;
        $update = $this->update;

        if ($command == 'select') {
            $query = "$explain SELECT $distinct $columns FROM $table $table_raw $join $where $group $having $raw $order $limit";
        } elseif ($command == 'insert') {
            $query = "INSERT $ignore INTO $table $insert";
        } elseif ($command == 'update') {
            $query = "UPDATE $ignore $table $join $update $where $raw $limit";
        } elseif ($command == 'delete') {
            $query = "DELETE FROM $table $table_raw $join $where $raw $limit";
        } else {
            return '';
        }

        return trim(preg_replace('/( ){2,}/', '$1', $query));
    }

    private function quotes(string $string, string $first = '`', string $last = '`', int|null $limit = null)
    {
        $string = str_replace([' as ', ' As ', ' aS '], ' AS ', $string);
        $separators = [' ', '.', ' AS '];
        $ignore = ['AS'];

        foreach ($separators as $separator) {
            if ($limit === null) {
                $values = explode($separator, $string);
            } else {
                $values = explode($separator, $string, $limit);
            }

            foreach ($values as $key => $value) {
                if (!in_array($value, $ignore)) {
                    $values[$key] = $first . trim($value, "$first\\$last") . $last;
                }
            }

            $string = implode($separator, $values);
        }

        return $string;
    }
}
