<?php

namespace Haley\Router;

class Middleware
{
    public bool $response = false;

    public function continue()
    {
        $this->response = true;
    }

    public function abort(int $status = 403, string|null $mesage = null)
    {
        return response()->abort($status, $mesage);
    }

    public function redirect(string|null $destination = null, $status = 302)
    {
        $this->response = false;

        return redirect($destination, $status);
    }
}
