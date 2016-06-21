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
            ->setDescription('seed')
            ->addArgument(
                'class',
                InputArgument::OPTIONAL,
                'glglgl'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $class = $input->getArgument('class');
        $file  = APPLICATION_PATH . '/config/migrations/' . $class . '.php';

        $output->write('migrating ' . $class . '... ');

        require_once $file;
        $migration = new $class();
        $migration->up();

        $output->writeln('ok');
    }
}
