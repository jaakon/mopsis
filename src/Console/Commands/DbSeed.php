<?php
namespace Mopsis\Console\Commands;

use Mopsis\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            )
            ->addOption(
                'override',
                null,
                InputOption::VALUE_NONE,
                'If set, existing classes will be overridden'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file      = $this->filesystem->findMigration($input->getArgument('migration'));
        $migration = $this->getMigration($file);
        $override  = !!$input->getOption('override');
        $table     = null;

        if ($table !== null) {
            if (!$override) {
                $output->writeln('<error>table already exists: ' . $table . '</error>');

                return;
            }

            $migration->down();
        }

        $output->write('migrating ' . get_class($migration) . '... ');
        $migration->up();
        $output->writeln('ok');
    }
}
