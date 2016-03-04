<?php
namespace Mopsis\Core;

use Illuminate\Config\Repository;

class Config extends Repository
{
    public function load(...$configFiles)
    {
        if (count($this->items)) {
            throw new \Exception('config already initialized');
        }

        foreach ($configFiles as $configFile) {
            $this->loadFile($configFile);
        }
    }

    protected function loadFile($configFile)
    {
        if (!file_exists($configFile)) {
            throw new \Exception('configuration file "' . $configFile . '" not found');
        }

        /**
         * @noinspection PhpIncludeInspection
         */
        $this->set(array_dot(include $configFile));
    }
}
