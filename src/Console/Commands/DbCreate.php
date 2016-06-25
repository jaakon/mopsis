<?php
namespace Mopsis\Console\Commands;

use Mopsis\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DbCreate extends Command
{
    protected function configure()
    {
        $this
            ->setName('db:create')
            ->setDescription('Create a database migration')
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
        $keys     = $this->identifyAction($input->getArgument('migration'));
        $template = $this->filesystem->findTemplateForMigration($keys['module']);
        $override = !!$input->getOption('override');

        $file = $this->filesystem->findMigration($keys['module']);

        if ($file !== null) {
            if (!$override) {
                $output->writeln('<error>file already exists: ' . $file . '</error>');

                return;
            }

            unlink($file);
        }

        $output->writeln(
            $this->filesystem->createMigration(
                $keys['module'],
                $this->stringHelper->fillTemplate($template, $keys),
                $override
            )
        );
    }
}
