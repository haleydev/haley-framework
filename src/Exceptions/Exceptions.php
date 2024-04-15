<?php

namespace Haley\Exceptions;

use Haley\Collections\Log;
use Error;
use ErrorException;
use Exception;
use InvalidArgumentException;
use PDOException;
use Throwable;
use TypeError;
use UnderflowException;

class Exceptions
{
    public function handler(callable $debug)
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
        });

        try {
            return call_user_func($debug);
        } catch (PDOException $error) {
            Log::create('database', $error->getMessage());
            return (new Debug)->exceptions($error);
        } catch (Throwable $error) {
            return (new Debug)->exceptions($error);
        } catch (Error $error) {
            return (new Debug)->exceptions($error);
        } catch (UnderflowException $error) {
            return (new Debug)->exceptions($error);
        } catch (InvalidArgumentException $error) {
            return (new Debug)->exceptions($error);
        } catch (Exception $error) {
            return (new Debug)->exceptions($error);
        } catch (TypeError $error) {
            return (new Debug)->exceptions($error);
        } catch (ErrorException $error) {
            return (new Debug)->exceptions($error);
        }
    }
}
