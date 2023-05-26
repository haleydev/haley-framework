<?php
namespace Haley\Database\Query;

use Haley\Database\Connection;
use Haley\Database\Query\Builder\Delete;
use Haley\Database\Query\Builder\Insert;
use Haley\Database\Query\Builder\Select;
use Haley\Database\Query\Builder;
use Haley\Database\Query\Builder\Update;

/**
 * @method static int teste(string $query, array $bindings = [])
 */
class DB
{
    /** 
     * GET CONNECTION INSTANCE
     */
    public static function connection(string $connection = 'default')
    {
        return Connection::instance($connection);
    }

    /** 
     * EXECUTE THE QUERY
     */
    public static function query(string $query, array $bindparams = [], string $connection = 'default')
    {
        $instance = Connection::instance($connection);
        $query = $instance->prepare($query);

        if (count($bindparams) > 0) {
            $count = 1;

            foreach ($bindparams as $value) {
                $query->bindValue($count, $value);
                $count++;
            }
        }

        $query->execute();

        return $query;
    }

    // /**   
    //  * QUERY SELECT
    //  */
    // public static function select(string $table, null|string $as = null)
    // {
    //     return (new Select)->from($table, $as);
    // }

    // /**   
    //  * QUERY INSERT
    //  */
    // public static function insert(string $table, null|string $as = null)
    // {
    //     return (new Insert)->into($table, $as);
    // }

    // /**   
    //  * QUERY UPDATE
    //  */
    // public static function update(string $table, null|string $as = null)
    // {
    //     return (new Update)->table($table, $as);
    // }

    // /**   
    //  * QUERY DELETE
    //  */
    // public static function delete(string $table, null|string $as = null)
    // {
    //     return (new Delete)->from($table, $as);
    // }

    /**   
     * EM TESTE
     */
    public static function table(string $table, null|string $as = null)
    {
        return (new Builder)->table($table, $as);
    }
}
