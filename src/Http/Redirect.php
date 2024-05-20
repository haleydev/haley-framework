<?php
namespace Haley\Http;

class Redirect
{
    public static function destination(string $destination, int $status = 302)
    {
        return header('Location: ' . $destination , true, $status);
    }

    /**
     * Redirecionar para uma uma rota nomeada
     */
    public static function route(string $route,int $status = 302)
    {
        return header('Location: ' . route($route) , true, $status);
    }

    /**
     * Redireciona para pagina anterior se existir ou pagina 404
     */
    public function back(int $status = 302)
    {
        if(isset($_SERVER['HTTP_REFERER'])){
            return header('Location: ' . $_SERVER['HTTP_REFERER'] , true, $status);
        }else{
            return response()->abort(404);
        }
    }
}