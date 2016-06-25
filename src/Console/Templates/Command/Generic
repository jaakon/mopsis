<?php
namespace Mopsis\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class __CLASS__Command extends Command
{
    protected function configure()
    {
        $this
            ->setName('__NAME__')
            ->setDescription('Create a default action class')
            ->addArgument(
                'action',
                InputArgument::REQUIRED,
                'What is the name of the action?'
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
        $output->writeln(
            $input->getOption('override')
        );
    }
}
