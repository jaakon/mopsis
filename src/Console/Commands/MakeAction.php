<?php
namespace Mopsis\Console\Commands;

use Symfony\Component\Console\Command\Command;
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
        list($module, $domain, $action) = explode('\\', $input->getArgument('action'));

        $output->writeln(
            createFile(
                $module . '/Action/' . $domain . $action . 'Action.php',
                findTemplateForAction($action),
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
