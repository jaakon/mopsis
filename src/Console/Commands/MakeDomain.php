<?php
namespace Mopsis\Console\Commands;

use Mopsis\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MakeDomain extends Command
{
    protected function configure()
    {
        $this
            ->setName('make:domain')
            ->setDescription('Create a new domain class')
            ->addArgument(
                'domain',
                InputArgument::REQUIRED,
                'What is the name of the domain?'
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
        $keys     = $this->identifyAction($input->getArgument('domain'));
        $template = $this->filesystem->findTemplateForDomain($keys['action']);

        $output->writeln(
            $this->filesystem->createClass(
                $keys['module'] . '/' . $keys['action'],
                $this->stringHelper->fillTemplate($template, $keys),
                $input->getOption('override')
            )
        );
    }
}
