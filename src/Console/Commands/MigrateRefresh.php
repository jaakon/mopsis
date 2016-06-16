<?php
namespace Mopsis\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateRefresh extends Command
{
    protected function configure()
    {
        $this
            ->setName('migrate:refresh')
            ->setDescription('Reset and re-run all migrations');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $files = glob('Migrations/*.php');

        foreach ($files as $file) {
            $class = basename($file, '.php');

            $output->write('migrating ' . $class . ' ... ');

            require_once $file;
            $migration = new $class();
            $migration->down();
            $migration->up();

            $output->writeln('ok');
        }
    }
}
