<?php
namespace Mopsis\Console;

use Mopsis\Console\Libraries\Filesystem;
use Mopsis\Console\Libraries\StringHelper;
use Symfony\Component\Console\Command\Command as ConsoleCommand;

class Command extends ConsoleCommand
{
    protected $filesystem;

    protected $stringHelper;

    public function __construct($name = null)
    {
        $this->filesystem   = new Filesystem();
        $this->stringHelper = new StringHelper();
        parent::__construct($name);
    }

    protected function getMigration($file)
    {
        $class = preg_replace('/.+\/\d+_(\w+)\.php$/', '$1', $file);

        require_once $file;

        return new $class();
    }

    protected function identifyAction($path)
    {
        list($module, $domain, $action) = explode('\\', $path);

        if ($action === null) {
            $action = $domain;
            $domain = $this->stringHelper->singularize($module);
        }

        return [
            'module'   => $module,
            'domain'   => $domain,
            'action'   => $action,
            'template' => $this->stringHelper->snakeCase($action)
        ];
    }
}
