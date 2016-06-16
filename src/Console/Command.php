<?php
namespace Mopsis\Console;

use Mopsis\Console\Libraries\Filesystem;
use Symfony\Component\Console\Command\Command as ConsoleCommand;

class Command extends ConsoleCommand
{
    protected $filesystem;

    public function __construct($name = null)
    {
        $this->filesystem = new Filesystem();
        parent::__construct($name);
    }
}
