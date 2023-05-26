<?php
namespace Core\Database\Migrations\Builder;

class TableOptions
{
    public function unique()
    {
        MigrationMemory::saveOption('unique');

        return $this;
    }

    public function default(string $default)
    {
        MigrationMemory::saveOption('default', [
            'default' => $default
        ]);

        return $this;
    }

    public function comment(string $comment)
    {
        MigrationMemory::saveOption('comment', [
            'comment' => $comment
        ]);

        return $this;
    }

    public function notNull()
    {
        MigrationMemory::saveOption('not_null');

        return $this;
    }
}
