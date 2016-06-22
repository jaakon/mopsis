<?php
namespace Mopsis\Console\Commands;

use Mopsis\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DbSeed extends Command
{
    protected function configure()
    {
        $this
            ->setName('db:seed')
            ->setDescription('Run a database migration')
            ->addArgument(
                'migration',
                InputArgument::REQUIRED,
                'What is the name of the migration?'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file      = APPLICATION_PATH . '/config/migrations/' . $input->getArgument('migration') . '.php';
        $migration = $this->getMigration($file);

        $output->write('migrating ' . get_class($migration) . '... ');
        $migration->up();
        $output->writeln('ok');
    }
}
