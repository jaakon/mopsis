<?php
namespace Mopsis\Console;

use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    public function addDefaultCommands()
    {
        foreach (glob(__DIR__ . '/Commands/*.php') as $file) {
            $class = __NAMESPACE__ . '\\Commands\\' . basename($file, '.php');
            $this->add(new $class());
        }
    }
}
