<?php
namespace Mopsis\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MakeController extends Command
{
    protected function configure()
    {
        $this
            ->setName('make:controller')
            ->setDescription('Create a new action class and its corresponding responder class')
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
        $config = [
            [
                'command'    => 'make:action',
                'action'     => $input->getArgument('action'),
                '--override' => $input->getOption('override')
            ],
            [
                'command'    => 'make:responder',
                'responder'  => $input->getArgument('action'),
                '--override' => $input->getOption('override')
            ]
        ];

        $this->generateClasses($config, $output);
    }

    protected function generateClasses($config, OutputInterface $output)
    {
        foreach ($config as $parameters) {
            $command = $this->getApplication()->find($parameters['command']);
            $input   = new ArrayInput($parameters);

            $command->run($input, $output);
        }
    }
}
