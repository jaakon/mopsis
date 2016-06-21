<?php
namespace Mopsis\Console\Commands;

use Mopsis\Console\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MakeModule extends Command
{
    protected function configure()
    {
        $this
            ->setName('make:module')
            ->setDescription('Create the structure and base classes for a new module')
            ->addArgument(
                'module',
                InputArgument::REQUIRED,
                'What is the name of the module?'
            )
            ->addOption(
                'crud',
                null,
                InputOption::VALUE_NONE,
                'If set, classes afor CRUD operations will be included'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = explode('\\', $input->getArgument('module'));

        foreach (['Action', 'Responder'] as $directory) {
            $output->writeln($this->filesystem->makeDirectory($path[0], $directory));
        }

        $config = !!$input->getOption('crud')
            ? $this->getCrudConfig($input->getArgument('module'))
            : $this->getBaseConfig($input->getArgument('module'));

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

    protected function getBaseConfig($module)
    {
        return [
            [
                'command' => 'make:action',
                'action'  => $module . '\\BareIndex'
            ],
            [
                'command' => 'make:domain',
                'domain'  => $module . '\\Filter'
            ],
            [
                'command' => 'make:domain',
                'domain'  => $module . '\\BareService'
            ],
            [
                'command'   => 'make:responder',
                'responder' => $module . '\\Index'
            ]
        ];
    }

    protected function getCrudConfig($module)
    {
        return [
            [
                'command' => 'make:action',
                'action'  => $module . '\\Index'
            ],
            [
                'command' => 'make:action',
                'action'  => $module . '\\Details'
            ],
            [
                'command' => 'make:action',
                'action'  => $module . '\\Create'
            ],
            [
                'command' => 'make:action',
                'action'  => $module . '\\Update'
            ],
            [
                'command' => 'make:action',
                'action'  => $module . '\\Delete'
            ],
            [
                'command' => 'make:domain',
                'domain'  => $module . '\\Entity'
            ],
            [
                'command' => 'make:domain',
                'domain'  => $module . '\\Filter'
            ],
            [
                'command' => 'make:domain',
                'domain'  => $module . '\\Gateway'
            ],
            [
                'command' => 'make:domain',
                'domain'  => $module . '\\Model'
            ],
            [
                'command' => 'make:domain',
                'domain'  => $module . '\\Repository'
            ],
            [
                'command' => 'make:domain',
                'domain'  => $module . '\\Service'
            ],
            [
                'command'   => 'make:responder',
                'responder' => $module . '\\Index'
            ],
            [
                'command'   => 'make:responder',
                'responder' => $module . '\\Details'
            ],
            [
                'command'   => 'make:responder',
                'responder' => $module . '\\Create'
            ],
            [
                'command'   => 'make:responder',
                'responder' => $module . '\\Update'
            ],
            [
                'command'   => 'make:responder',
                'responder' => $module . '\\Delete'
            ]
        ];
    }
}
