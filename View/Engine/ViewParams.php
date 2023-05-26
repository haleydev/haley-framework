<?php
namespace Core\View\Engine;

class ViewParams
{ 
    public static array|object $params = [];

    public static function params(array|object $params = [])
    {   
        if(is_object($params)){
           self::$params = get_object_vars($params);
        }else{
           self::$params = $params;
        } 

        return;
    }
}