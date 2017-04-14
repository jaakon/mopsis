<?php
namespace Mopsis\Console\Commands;

use Mopsis\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Mopsis\Console\Libraries\Schema;

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
        $files = glob(MIGRATIONS_PATH . '/*.php');

        Schema::disableForeignKeyConstraints();

        foreach (array_reverse($files) as $file) {
            $migration = $this->getMigration($file);
            $output->write('dropping ' . get_class($migration) . '... ');
            $migration->down();
            $output->writeln('ok');
        }

        foreach ($files as $file) {
            $migration = $this->getMigration($file);
            $output->write('creating ' . get_class($migration) . '... ');
            $migration->up();
            $output->writeln('ok');
        }

        Schema::enableForeignKeyConstraints();
    }
}
