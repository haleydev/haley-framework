<?php

namespace Haley\Console\Commands;

use Haley\Console\Lines;
use Haley\Database\DB;
use Haley\Database\Migration\Helper;
use Haley\Database\Migration\Builder\BuilderMemory;

class CommandMigration extends Lines
{
    private $migration = null;

    private BuilderMemory|null $build = null;
    private Helper|null $helper = null;
    private string|null $file = null;
    private bool $edit = false;
    private bool $changes = false;

    public function run(string|null $name = null)
    {
        if ($name !== null) {
            $file = directoryRoot('database/migrations/' . $name . '.php');

            if (!file_exists($file)) return $this->red('file not found')->blue($file)->br();

            $migration_files = [$name . '.php'];
        } else {
            $migration_files = array_diff(scandir(directoryRoot('database/migrations')), ['.', '..']);
        }

        foreach ($migration_files as $file) {
            $this->migration = require directoryRoot('database/migrations/' . $file);
            if (!$this->migration->active) continue;

            $this->file = $file;
            $this->migration->up();
            $this->build = new BuilderMemory;
            $this->build::compileForeigns();

            $this->helper = DB::helper($this->build::$connection);

            if ($this->helper->table()->has($this->build::$table)) {
                $this->edit = true;
                $this->runEdit();
            } else {
                $this->edit = false;
                $this->runCreate();
            }

            $this->runConstraints();

            $this->build::reset();
        }
    }

    private function runCreate()
    {
        $columns = [];

        foreach ($this->build::getColumns() as $value) {
            $columns[$value['name']] = str_replace(['[CL:NAME]', '[CL:TYPE]'], [$value['name'], $value['type']], $value['query']);
        }

        $this->helper->table()->create($this->build::$table, $columns);

        $this->green($this->file)->blue('table created')->br();
    }

    private function runEdit()
    {
        $columns_names = [];

        // rename columns
        foreach ($this->build::$rename as $column => $to) {
            if ($this->helper->column()->has($this->build::$table, $column) and !$this->helper->column()->has($this->build::$table, $to)) {
                $renamed = $this->helper->column()->rename($this->build::$table, $column, $to);

                if ($renamed) $this->green($this->build::$table . ':' . $column)->blue('renamed to ' . $to)->br();
            }
        }

        // change or create columns
        foreach ($this->build::getColumns() as $value) {
            $type = trim(str_replace(['[CL:NAME]', '[CL:TYPE]'], ['', $value['type']], $value['query']));

            if ($this->helper->column()->has($this->build::$table, $value['name'])) {
                $modified = $this->helper->column()->change($this->build::$table, $value['name'], $type);
                if ($modified) $this->green($this->build::$table . ':' . $value['name'])->blue('modified')->br();
            } else {
                $created = $this->helper->column()->create($this->build::$table, $value['name'], $type);
                if ($created) $this->green($this->build::$table . ':' . $value['name'])->blue('added')->br();
            }

            $columns_names[] = $value['name'];
        }
    }

    private function runConstraints()
    {
        // column id primary key
        if (count($this->build::$id)) {
            $change = $this->helper->constraint()->setId($this->build::$table, $this->build::$id['name'], $this->build::$id['comment']);

            // if($change and $this->edit) 
        }

        // column primary key
        elseif ($this->build::$primary !== null) {
            $atual_primary = $this->helper->constraint()->getPrimaryKey($this->build::$table);

            if ($atual_primary !== $this->build::$primary) {
                $this->helper->constraint()->dropPrimaryKey($this->build::$table);              
            }

            $this->helper->constraint()->setPrimaryKey($this->build::$table, $this->build::$primary);
        }

        // set constraints   
        $constraints_active = [];

        foreach ($this->build::$constraints as $value) {
            $constraints_active[] = $value['name'];

            if (!$this->helper->constraint()->has($this->build::$table, $value['name'])) {
                $this->helper->constraint()->create($this->build::$table, $value['name'], $value['type'], $value['value']);
            } else {
                // if ($value['type'] == 'FOREIGN KEY') 
                $this->helper->constraint()->change($this->build::$table, $value['name'], $value['type'], $value['value']);
            }
        }

        if (!$this->migration->single) return;


        // remove unused constraints
        foreach ($this->build::getColumns() as $x) {
         

            $constraints_check = $this->helper->constraint()->getNamesByColumn($this->build::$table, $x['name']);      

            var_dump( $this->helper->constraint()->getNames($this->build::$table));

            // if (!empty($constraints_check)) {
            //     foreach ($constraints_check as $y) {    
            //         $this->red($y)->br();
                    
            //         if (!in_array($y, $constraints_active)) {
            //             $this->helper->constraint()->drop($this->build::$table, $y);                      
            //         }
            //     }
            // }
        }


    }
}
