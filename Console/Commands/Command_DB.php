<?php
namespace Core\Console\Commands;
use Core\Console\Lines;
use Core\Database\Migrations\Builder\MigrationMemory;
use Core\Database\Migrations\Migration;

class Command_DB extends Lines
{   
    public function migrate()
    {
        $this->red('migrating ...');

        (new Migration)->migrate();    
        
        $this->displayMessages(MigrationMemory::$messages);

        $this->green('migrations completed');
    }

    public function seeder()
    {
        $this->red('seeding ...');

        (new Migration)->seeders();

        $this->displayMessages(MigrationMemory::$messages);

        $this->green('seeders completed');
    }

    public function drop(string $table)
    {
        $result = (new Migration)->dropTable($table);

        if(is_string($result)) {
            $this->red($result);
        }elseif($result == true){
            $this->green("Success, table $table dropped");
        }

        die;
    }

    // public function list_migrations()
    // {
    //     // if(!empty(DB::query("SHOW TABLES LIKE ?",['migrations'])->rowCount())) {
    //     //     $migrations = DB::select('migrations')
    //     //     ->columns(['migration','table_name','action','migration_date'])
    //     //     ->execute();

    //     //     if($migrations){
    //     //         foreach ($migrations as $migration) {
    //     //             $this->white($migration['migration_date'] . ' (' . $migration['table_name'] . ' -- ' . $migration['migration'] . ')--> ' . $migration['action']);                    
    //     //         }  
                
    //     //         return;
    //     //     } else {
    //     //         $this->green('nenhuma migration encontrada');
    //     //         return;                    
    //     //     }
    //     // } else {
    //     //     $this->red('nenhuma migration encontrada');
    //     //     return;
    //     // }
    // }

    // private function addModels()
    // {
    //     $alteration = false;
    //     $model = new Command_Model;
    //     $tables = DB::query("SHOW TABLES")->fetchAll(PDO::FETCH_ASSOC);       
    //     foreach($tables as $array) {
    //         $table = $array[array_key_first($array)];

    //         if($table != 'migrations') {
    //             $execute = $model->model($table,false);
    //             if($execute == true) {
    //                 $alteration = true;
    //                 $this->white('nova model adicionada: ' . $table);
    //             }
    //         }
    //     }

    //     return $alteration;
    // }

    private function displayMessages(array $messages)
    { 
        foreach($messages as $migration => $message) {
            foreach($message as $values) {
                $this->blue("$migration::{$values['table']}",false);
                
                if($values['error'] == true){              
                    $this->red($values['mesage']);               
                }

                if($values['error'] == false){            
                    $this->green($values['mesage']);
                }
                
            }
        }
    }
}