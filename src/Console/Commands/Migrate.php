<?php
namespace Mopsis\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Migrate extends Command
{
    protected function configure()
    {
        $this
            ->setName('migrate')
            ->setDescription('Run a database migration')
            ->addArgument(
                'migration',
                InputArgument::REQUIRED,
                'What is the name of the migration?'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file  = 'Migrations/' . $input->getArgument('migration') . '.php';
        $class = $input->getArgument('migration');

        $output->write('migrating ' . $class . ' ... ');

        require_once $file;
        $migration = new $class();
        $migration->up();

        $output->writeln('ok');
    }
}
