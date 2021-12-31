<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Command;

use Camelot\Arbitration\Filesystem\Filesystem;
use Camelot\Arbitration\Filesystem\Supervisor;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Path;
use function is_string;
use function sprintf;
use function str_replace;

abstract class AbstractSupervisorCommand extends Command
{
    protected const TYPE_FILE = 'file';
    protected const TYPE_RENDITION = 'rendition';
    protected const TYPE_SET = 'set';

    protected Supervisor $supervisor;
    protected Filesystem $imagesFilesystem;
    protected string $projectDir;

    public function __construct(Supervisor $supervisor, Filesystem $imagesFilesystem, string $projectDir)
    {
        parent::__construct();
        $this->supervisor = $supervisor;
        $this->imagesFilesystem = $imagesFilesystem;
        $this->projectDir = $projectDir;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $type = $input->getArgument('type');
        if ($type !== self::TYPE_FILE && $type !== self::TYPE_RENDITION && $type !== self::TYPE_SET) {
            throw new RuntimeException(sprintf('One of "%s", "%s", or "%s" is required as the first argument', self::TYPE_FILE, self::TYPE_RENDITION, self::TYPE_SET));
        }
    }

    protected function getFinderPath(null|string|array $paths, bool $pattern = true): null|string|array
    {
        if (!$paths) {
            return null;
        }

        if (is_string($paths)) {
            if ($pattern) {
                return '/^' . str_replace('/', '\/', $this->makeRelative($paths)) . '/';
            }

            return $this->makeRelative($paths);
        }

        $arr = [];
        foreach ($paths as $path) {
            if ($pattern) {
                $arr[] = '/^' . str_replace('/', '\/', $this->makeRelative($path)) . '/';
            } else {
                $arr[] = $this->makeRelative($path);
            }
        }

        return $arr;
    }

    private function makeRelative(?string $path): string
    {
        return Path::makeRelative(Path::join($this->projectDir, $path), $this->imagesFilesystem->getBasePath());
    }
}
