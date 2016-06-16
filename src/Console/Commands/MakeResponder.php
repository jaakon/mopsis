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
        list($module, $domain, $action) = explode('\\', $input->getArgument('responder'));

        $output->writeln(
            $this->filesystem->createFile(
                $module . '/Responder/' . $domain . $action . 'Responder.php',
                $this->filesystem->findTemplateForResponder($action),
                [
                    '{{MODULE}}'     => $module,
                    '{{DOMAIN}}'     => $domain,
                    '{{ACTION}}'     => $action,
                    '{{TEMPLATE}}'   => $this->filesystem->snakeCase($action),
                    '{{COLLECTION}}' => strtolower($module),
                    '{{INSTANCE}}'   => strtolower($domain)
                ],
                $input->getOption('override')
            )
        );
    }
}
