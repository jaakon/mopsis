<?php
namespace Mopsis\Console\Commands;

use Mopsis\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MakeResponder extends Command
{
    protected function configure()
    {
        $this
            ->setName('make:responder')
            ->setDescription('Create a new responder class')
            ->addArgument(
                'responder',
                InputArgument::REQUIRED,
                'What is the name of the responder?'
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
        $keys     = $this->identifyAction($input->getArgument('responder'));
        $template = $this->filesystem->findTemplateForResponder($keys['action']);

        $output->writeln(
            $this->filesystem->createClass(
                $keys['module'] . '/Responder/' . $keys['action'] . 'Responder',
                $this->stringHelper->fillTemplate($template, $keys),
                $input->getOption('override')
            )
        );
    }
}
