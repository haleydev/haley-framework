<?php

namespace Haley\Database\Query;

use Haley\Database\Connection;

class RunQuery
{
    public function select(string $query, array $bindparams, string $connection, bool $all = true, $count = false)
    {
        $instance = Connection::instance($connection);
        $query = $instance->prepare($query);

        if (count($bindparams) > 0) {
            $count_bindparams = 1;

            foreach ($bindparams as $value) {
                $query->bindValue($count_bindparams, $value);
                $count_bindparams++;
            }
        }

        $query->execute();

        if ($count == true) {
            return $query->rowCount();
        }

        if ($all == true) {
            return $query->fetchAll();
        } else {
            return $query->fetch();
        }
    }

    public function insert(string $query, array $bindparams, string $connection, $get_id = false)
    {
        $instance = Connection::instance($connection);
        $query = $instance->prepare($query);

        if (count($bindparams) > 0) {
            $count_bindparams = 1;

            foreach ($bindparams as $value) {
                $query->bindValue($count_bindparams, $value);
                $count_bindparams++;
            }
        }

        $query->execute();

        if ($get_id) {
            return $instance->lastInsertId();
        }

        return $query->rowCount();
    }

    public function update(string $query, array $bindparams, string $connection)
    {
        $instance = Connection::instance($connection);
        $query = $instance->prepare($query);

        if (count($bindparams) > 0) {
            $count_bindparams = 1;

            foreach ($bindparams as $value) {
                $query->bindValue($count_bindparams, $value);
                $count_bindparams++;
            }
        }

        $query->execute();

        return $query->rowCount();
    }

    public function delete(string $query, array $bindparams, string $connection)
    {
        $instance = Connection::instance($connection);
        $query = $instance->prepare($query);

        if (count($bindparams) > 0) {
            $count_bindparams = 1;

            foreach ($bindparams as $value) {
                $query->bindValue($count_bindparams, $value);
                $count_bindparams++;
            }
        }

        $query->execute();

        return $query->rowCount();
    }
}
