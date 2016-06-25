<?php
namespace Mopsis\Console\Commands;

use Mopsis\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MakeView extends Command
{
    protected function configure()
    {
        $this
            ->setName('make:view')
            ->setDescription('Create a new view for an action')
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
        list($module, $domain, $action) = explode('\\', $input->getArgument('action'));

        $output->writeln(
            $this->filesystem->createView(
                $module . '/' . $this->stringHelper->snakeCase($action) . '.twig',
                $this->filesystem->findTemplateForView($action),
                [
                    '{{MODULE}}'   => $module,
                    '{{DOMAIN}}'   => $domain,
                    '{{ACTION}}'   => $action,
                    '{{INSTANCE}}' => strtolower($domain)
                ],
                $input->getOption('override')
            )
        );
    }
}
