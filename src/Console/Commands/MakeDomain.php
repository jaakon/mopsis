<?php
namespace Mopsis\Console\Commands;

use Symfony\Component\Console\Command\Command;
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
        list($module, $domain, $type) = explode('\\', $input->getArgument('domain'));

        $output->writeln(
            createFile(
                $module . '/Domain/' . $domain . $type . '.php',
                findTemplateForDomain($type),
                [
                    '{{MODULE}}'   => $module,
                    '{{DOMAIN}}'   => $domain,
                    '{{INSTANCE}}' => strtolower($domain)
                ],
                $input->getOption('override')
            )
        );
    }
}
