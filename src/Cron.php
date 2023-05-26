<?php
namespace Haley;
use Throwable;

class Cron
{    
    private $minute;
    private $hours;
    private $day;
    private $month;

    private $actions = [];
    private $descriptions = [];
    private $count = 1;

    public function __construct()
    {
        $this->seconds = date('s');
        $this->minute = date('i');
        $this->hours = date('H');
        $this->day = date('d');
        $this->month = date('m');        
    }   

    /**
     * Data especifica
     * @param string $date d/m/Y
     * @param string $hours H:i
     */
    public function cron(string $hours = '00:00', string $date = '01/01/2022', callable|array $action)
    {
        $cronjob = date('H:i d/m/Y');
        if($cronjob == $hours . ' ' . $date) {
            $this->actions[$this->count] = $action;
        }

        $this->count ++;
        return $this;
    }

    /**
     * A cada minuto
     */
    public function everyMinute(int $minute, callable|array $action)
    {
        if($this->clock($minute, 'm')){ 
            $this->actions[$this->count] = $action;
        }

        $this->count ++;
        return $this;
    }

    /**
     * A cada hora
     */
    public function everyHour(int $hour, callable|array $action)
    {
        if($this->clock($hour, 'h')){
            $this->actions[$this->count] = $action;
        }

        $this->count ++;
        return $this;
    }

    /**
     * Todo dia do mês
     * @param int $day
     * @param string $hours H:i
     */
    public function everyMonth(int $day, string $hours = '00:00', callable|array $action)
    {
        if($this->day == $day){
            if($hours == $this->hours.":".$this->minute){
                $this->actions[$this->count] = $action;  
            }
        }

        $this->count ++;
        return $this;
    }

    /**
     * Uma vez ao dia
     * @param string $hours H:i
     */
    public function dailyAt(string $hours = '00:00', callable|array $action)
    {
        $date = explode(':',$hours,2);
        if($date[0] == $this->hours and $date[1] == $this->minute){
            $this->actions[$this->count] = $action;  
        }

        $this->count ++;
        return $this;
    }

    /**
     * Primeiro dia do ano
     */
    public function yearly(callable|array $action)
    {       
        if($this->hours == 00 and $this->minute == 00 and $this->day == 01 and $this->month == 01){
            $this->actions[$this->count] = $action; 
        }

        $this->count ++;
        return $this;
    }

    private function clock(int $value, string $type)
    {
        $return = false;

        if ($type == 'm') {
            $a = 60;
            $keys = "";

            while ($a >= $value) {
                $keys .= "$a,";
                $a = $a - $value;
            }

            $clock = str_replace("60", "00", rtrim($keys, ","));

            foreach (explode(',', $clock) as $t) {
                if ($t == date('i')) {
                    $return = true;
                }
            }            
        }

        if ($type == 'h') {  
            if(date('i') == '00'){
                $a = 24;
                $keys = "";
                while ($a >= $value) {
                    $keys .= "$a,";
                    $a = $a - $value;
                }
             
                $clock = str_replace("24", "00", rtrim($keys, ",")); 

                foreach (explode(',', $clock) as $t) {
                    if ($t == date('G')) {
                        $return = true;
                    }
                }  
            }
        }

        return $return;
    }

    public function description(string $description)
    {
        $this->descriptions[$this->count - 1] = $description;
    }

    public function execute()
    {  
        foreach($this->actions as $key => $value){ 
            try {
                if (is_array($value)) {
                    $value[0] = new $value[0]();
                }

                if(isset($this->descriptions[$key])){               
                    $text = "[" . date('d/m/Y h:i:s') . "] ".$this->descriptions[$key]." - status: iniciado". PHP_EOL;
                    file_put_contents(dirname(__DIR__).'/app/logs/cronjob.log', $text, FILE_APPEND);
                }else{
                    $text = "[" . date('d/m/Y h:i:s') . "] ??? - status: iniciado". PHP_EOL;
                    file_put_contents(dirname(__DIR__).'/app/logs/cronjob.log', $text, FILE_APPEND);
                }

                // executa
                if(is_callable($value)){
                    call_user_func($value);
                }                
                
                if(isset($this->descriptions[$key])){               
                    $text = "[" . date('d/m/Y h:i:s') . "] ".$this->descriptions[$key]." - status: concluido". PHP_EOL;
                    file_put_contents(dirname(__DIR__).'/app/logs/cronjob.log', $text, FILE_APPEND);
                }else{
                    $text = "[" . date('d/m/Y h:i:s') . "] ??? - status: concluido". PHP_EOL;
                    file_put_contents(dirname(__DIR__).'/app/logs/cronjob.log', $text, FILE_APPEND);
                }

            } catch (Throwable $e) {
                if(isset($this->descriptions[$key])){    
                    $text = "[" . date('d/m/Y h:i:s') . "] ".$this->descriptions[$key]." - error: " . $e->getMessage() . PHP_EOL;
                    file_put_contents(dirname(__DIR__).'/app/logs/cronjob.log', $text, FILE_APPEND);                   
                }else{
                    $text = "[" . date('d/m/Y h:i:s') . "] ??? - error: " . $e->getMessage() . PHP_EOL;
                    file_put_contents(dirname(__DIR__).'/app/logs/cronjob.log', $text, FILE_APPEND);                       
                }         
            }           
        }  
    }
}