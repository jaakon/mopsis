<?php
namespace Mopsis\Console\Commands;

use Mopsis\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MakeAction extends Command
{
    protected function configure()
    {
        $this
            ->setName('make:action')
            ->setDescription('Create a new action class')
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
        $keys     = $this->identifyAction($input->getArgument('action'));
        $template = $this->filesystem->findTemplateForAction($keys['action']);

        $output->writeln(
            $this->filesystem->createClass(
                $keys['module'] . '/Action/' . $keys['action'] . 'Action',
                $this->stringHelper->fillTemplate($template, $keys),
                $input->getOption('override')
            )
        );
    }
}
