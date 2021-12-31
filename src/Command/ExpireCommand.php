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
    name: 'arbitration:expire',
    description: 'Expire rendered images for a source image, rendition, or rendition set.',
)]
final class ExpireCommand extends AbstractSupervisorCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument('type', InputArgument::REQUIRED, 'Either "file", "rendition", or "set"')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the file, rendition, or set')
            ->addOption('replace', null, InputOption::VALUE_NONE, 'Re-render files. Only valid for "file" actions')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $type = $input->getArgument('type');
        $name = $input->getArgument('name');
        $replace = $input->getOption('replace');

        if ($type === self::TYPE_FILE) {
            $io->title('Removing rendered files for source file:' . $name);
            $this->supervisor->expireFile($this->getFinderPath($name), $replace);
        }
        if ($type === self::TYPE_RENDITION) {
            $io->title('Removing rendered files for rendition:' . $name);
            $this->supervisor->expireRendition($name);
        }
        if ($type === self::TYPE_SET) {
            $io->title('Removing rendered files for rendition set: ' . $name);
            $this->supervisor->expireSet($name);
        }

        $io->success('Complete.');

        return Command::SUCCESS;
    }
}
