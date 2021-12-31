<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'arbitration:verify',
    description: 'Verify rendered images against a source image and replace if required.',
)]
final class VerifyCommand extends AbstractSupervisorCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument('path', InputArgument::OPTIONAL, 'Directory path name to verify files in.')
            ->addOption('remove', 'r', InputOption::VALUE_NONE, 'Remove orphaned render files, i.e. those without a matching source file')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $path = $input->getArgument('path');

        $io->title("Verifying {$path} against source files");
        $this->supervisor->verify($this->getFinderPath($path, false), $input->getOption('remove'));

        $io->success('Complete.');

        return Command::SUCCESS;
    }
}
