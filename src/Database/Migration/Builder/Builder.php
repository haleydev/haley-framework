<?php

namespace Haley\Database\Migration\Builder;

use Haley\Collections\Log;
use InvalidArgumentException;

class Builder
{
    private array|null $config = null;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function id(string $name = 'id', string|null $comment = null)
    {
        if (!empty(BuilderMemory::$primary) or count(BuilderMemory::$id)) {
            Log::create('migration', 'Table ' . BuilderMemory::$table . ' must have only one primary key');
            throw new InvalidArgumentException('Table ' . BuilderMemory::$table . ' must have only one primary key');
        }

        BuilderMemory::$id = [
            'name' => $name,
            'comment' => $comment
        ];
    }

    public function varchar(string $name, int $size = 255)
    {
        if (in_array($this->config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            $type = sprintf('varchar(%s)', $size);
        } else {
            return $this->typeError('varchar');
        }

        BuilderMemory::addColumn($name, $type);

        return new BuilderOptions($this->config['driver']);
    }

    public function int(string $name, int|null $size = null)
    {
        if (in_array($this->config['driver'], ['mysql', 'pgsql', 'mariadb'])) {
            $type = sprintf($size !== null ? 'INT(%s)' : 'INT', $size);
        } else {
            return $this->typeError('int');
        }

        BuilderMemory::addColumn($name, $type);

        return new BuilderOptions($this->config['driver']);
    }

    public function rename(string $column, string $to)
    {
        BuilderMemory::$rename[$column] = $to;
    }

    private function typeError(string $type)
    {
        Log::create('migration', 'Driver not found for ' . $type);
        throw new InvalidArgumentException('Driver not found for ' . $type);
    }
}
