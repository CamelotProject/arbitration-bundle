<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function implode;

#[AsCommand(
    name: 'arbitration:prime',
    description: 'Prime rendered images for a source image, rendition, or rendition set.',
)]
final class PrimeCommand extends AbstractSupervisorCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument('type', InputArgument::REQUIRED, 'Either "rendition" or "set"')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the rendition or set')
            ->addArgument('path', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Path names to process')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $type = $input->getArgument('type');
        $name = $input->getArgument('name');
        $paths = $input->getArgument('path');

        if ($type === self::TYPE_RENDITION) {
            $io->title("Priming rendition $name for " . implode(' ', $paths));
            foreach ($this->getFinderPath($paths) as $path) {
                $io->title("Priming rendition '{$name}' files for source(s) " . implode(' ', $paths));
                $this->supervisor->primeRendition($name, $this->getFinderPath($paths));
            }
        }
        if ($type === self::TYPE_SET) {
            foreach ($this->getFinderPath($paths) as $path) {
                $io->title("Priming rendition set '{$name}' for source(s) " . implode(' ', $paths));
                $this->supervisor->primeSet($name, $this->getFinderPath($paths));
            }
        }

        $io->success('Complete.');

        return Command::SUCCESS;
    }
}
