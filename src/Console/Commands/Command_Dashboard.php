<?php
namespace Haley\Console\Commands;
use Haley\Console\Lines;

class Command_Dashboard extends Lines
{   
    public function dashboard()
    { 
        echo "\033[0;34m mcquery v1.0.0 beta - Warley Rodrigues de Moura\033[0m". PHP_EOL . PHP_EOL; 

        echo "\033[0;33m comandos disponiveis\033[0m" . PHP_EOL;
        echo "\033[0;32m server\033[0m ativa servidor de desenvolvimento mcquery - server:port 0000 para escolher uma porta" . PHP_EOL;
        echo "\033[0;32m cronjob\033[0m ativa/desativa o cronjob 'linux debian' - " . $this->cron_check() . PHP_EOL;

        echo "\033[0;33m create\033[0m" . PHP_EOL;        
        echo "\033[0;32m create:controller nome\033[0m cria um novo controller - adicione 'pasta/NomeController' caso queira adicionar uma subpasta" . PHP_EOL;
        echo "\033[0;32m create:class nome\033[0m cria uma nova classe - adicione 'pasta/NomeClasse' caso queira adicionar uma subpasta" . PHP_EOL;  
        echo "\033[0;32m create:model nome\033[0m cria um novo model" . PHP_EOL;
        echo "\033[0;32m create:database nome\033[0m cria uma nova base de dados" . PHP_EOL;
        echo "\033[0;32m create:job nome\033[0m cria um novo arquivo de tarefas cronjob" . PHP_EOL; 
        echo "\033[0;32m create:middleware nome\033[0m cria um novo middleware" . PHP_EOL;
        echo "\033[0;32m create:env\033[0m cria um novo arquivo de configurações" . PHP_EOL . PHP_EOL;;

        echo "\033[0;33m database\033[0m" . PHP_EOL;
        echo "\033[0;32m db:migrate\033[0m executa as bases de dados/models pendentes" . PHP_EOL;
        echo "\033[0;32m db:seeder\033[0m executa os seeders pendentes" . PHP_EOL;
        echo "\033[0;32m db:drop nome\033[0m exclui uma tabela do banco de dados" . PHP_EOL;
        echo "\033[0;32m db:conexao\033[0m testa a conexão com o banco de dados" . PHP_EOL;
        echo "\033[0;32m db:list\033[0m lista todas as migrações já executadas" . PHP_EOL . PHP_EOL;

        echo "\033[0;33m cache\033[0m" . PHP_EOL;
        echo "\033[0;32m cache:view\033[0m limpa o cache dos view" . PHP_EOL;     
        echo "\033[0;32m cache:env\033[0m armazena e usa as informações do .env em cache - " . $this->env_check() . PHP_EOL . PHP_EOL;
        
   
    }

    private function env_check()
    {
        if (file_exists(ROOT.'/app/cache/env.json')) {
            return "\033[0;32mativo\033[0m" . PHP_EOL;
        } else {
            return "\033[0;31mdesativado\033[0m" . PHP_EOL;;
        }
    }

    private function cron_check()
    {
        if(strtolower(PHP_OS) == 'linux'){   
            $service = shell_exec('service cron status 2>&1');

            if(str_contains($service,'cron is running') or str_contains($service,'active (running)') ){             
              
                $check = shell_exec('crontab -l 2>&1');

                if(str_contains($check,'* * * * * cd '. ROOT .' && php mcquery cronjob:run >> /dev/null 2>&1')){
                    return "\033[0;32mativo\033[0m" . PHP_EOL; 
                }
            }          
        }   

        return "\033[0;31mdesativado\033[0m" . PHP_EOL;
    }
}