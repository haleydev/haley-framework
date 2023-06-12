<?php

namespace Haley\Database\Query\Grammars;

class GrammarsCollections
{  
    public static function operators(string $driver)
    {
        if (in_array($driver, ['mysql', 'pgsql', 'mariadb'])) {
            return [
                '=', '<', '>', '<=', '>=', '<>', '!=', '<=>',
                'like', 'like binary', 'not like', 'ilike',
                '&', '|', '^', '<<', '>>', '&~', 'is', 'is not',
                'rlike', 'not rlike', 'regexp', 'not regexp',
                '~', '~*', '!~', '!~*', 'similar to',
                'not similar to', 'not ilike', '~~*', '!~~*'
            ];
        }

        return [];
    }
}
