<?php
namespace Haley\Validator;

/**
 * MCQUERY VALIDATOR
 */
class ValidatorHelper
{
    /**
     * Retorna um array com todos os erros do validator
     * @return array|false
     */
    public function all($input = null)
    {
        if(isset($_SERVER['HTTP_REFERER'])){
            $page = $_SERVER['HTTP_REFERER']; 
    
            if(isset($_SESSION['VALIDATOR'][$page]['ERRORS'])){
                if($_SESSION['VALIDATOR'][$page]['ERRORS'] != false){
                    $all_errors = $_SESSION['VALIDATOR'][$page]['ERRORS'];  

                    if($input == null) {
                        return $all_errors;
                    }elseif(isset($all_errors[$input])){
                        return $all_errors[$input];
                    }
                }
            }
        }   
    
        return false;
    }

    /**
     * Retorna o primeiro erro, se for especificado retorna o primeiro erro do input
     * @return string|false
     */
    public function first(string $input = null)
    {
        if(isset($_SERVER['HTTP_REFERER'])){
            $page = $_SERVER['HTTP_REFERER']; 
    
            if(isset($_SESSION['VALIDATOR'][$page]['ERRORS'])){
                if($_SESSION['VALIDATOR'][$page]['ERRORS'] != false){
                    $all_errors = $_SESSION['VALIDATOR'][$page]['ERRORS'];                      
                    if($input == null) {
                        $first = reset($all_errors);
                        if(isset($first[0])) {
                            return $first[0];
                        }
                    }else{
                        if(isset($all_errors[$input])) {
                            if(isset($all_errors[$input][0])){
                                return $all_errors[$input][0];
                            }                           
                        }                      
                    }                   
                }
            }
        }   
    
        return false;
    }

    /**
     * Retorna o valor do input filtrado por validator
     * @return string|false
     */
    public function value(string $input)
    {
        if(isset($_SERVER['HTTP_REFERER'])){
            $page = $_SERVER['HTTP_REFERER'];
    
            if(isset($_SESSION['VALIDATOR'][$page]['INPUTS'])){
                if(isset($_SESSION['VALIDATOR'][$page]['INPUTS'][$input])){   
                    return $_SESSION['VALIDATOR'][$page]['INPUTS'][$input];             
                }        
            }
        }
     
        return false;
    }
}