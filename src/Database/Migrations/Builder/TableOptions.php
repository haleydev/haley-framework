<?php
namespace Haley\Database\Migrations\Builder;

class TableOptions
{
    public function unique()
    {
        MigrationMemory::option('unique');

        return $this;
    }

    public function default(string $default)
    {
        MigrationMemory::option('default', [
            'default' => $default
        ]);

        return $this;
    }

    public function comment(string $comment)
    {
        MigrationMemory::option('comment', [
            'comment' => $comment
        ]);

        return $this;
    }

    public function notNull()
    {
        MigrationMemory::option('not_null');

        return $this;
    }
}
