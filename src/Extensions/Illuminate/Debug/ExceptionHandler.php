<?php
namespace Mopsis\Extensions\Illuminate\Debug;

use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;

class ExceptionHandler implements ExceptionHandlerContract
{
    protected $handlers = [];

    public function render($request, Exception $exception)
    {
        return $exception->getMessage();
    }

    public function renderForConsole($output, Exception $exception)
    {
        echo $exception->getMessage();
    }

    public function report(Exception $exception) {}
}
