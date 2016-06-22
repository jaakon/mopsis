<?php
namespace Mopsis\Console\Commands;

use Mopsis\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DbRefresh extends Command
{
    protected function configure()
    {
        $this
            ->setName('db:refresh')
            ->setDescription('Reset and re-run all migrations');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $files = glob(APPLICATION_PATH . '/config/migrations/*.php');

        foreach ($files as $file) {
            $migration = $this->getMigration($file);

            $output->write('migrating ' . get_class($migration) . '... ');
            $migration->down();
            $migration->up();
            $output->writeln('ok');
        }
    }
}
