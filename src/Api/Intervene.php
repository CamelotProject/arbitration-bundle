<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Api;

use Camelot\Arbitration\Manipulators\ManipulatorInterface;
use Camelot\Arbitration\Manipulators\ManipulatorsFactory;
use Intervention\Image\ImageManager;
use InvalidArgumentException;
use function sprintf;

final class Intervene implements InterveneInterface
{
    /** Intervention image manager. */
    private ImageManager $imageManager;
    /** Collection of manipulators. */
    private iterable $manipulators;

    /**
     * Create API instance.
     *
     * @param ImageManager $imageManager intervention image manager
     * @param iterable     $manipulators collection of manipulators
     */
    public function __construct(ImageManager $imageManager, iterable $manipulators = null)
    {
        $this->imageManager = $imageManager;
        $this->manipulators = $manipulators ?: ManipulatorsFactory::create();
    }

    /**
     * Perform image manipulations.
     *
     * @param string $source source image binary data
     * @param array  $params the manipulation params
     *
     * @return string manipulated image binary data
     */
    public function handle(string $source, array $params): string
    {
        $image = $this->imageManager->make($source);

        foreach ($this->manipulators as $manipulator) {
            if (!$manipulator instanceof ManipulatorInterface) {
                throw new InvalidArgumentException(sprintf('Manipulator %s does not implement %s.', $manipulator::class, ManipulatorInterface::class));
            }
            $manipulator->setParams($params);

            $image = $manipulator->run($image);
        }

        return $image->getEncoded();
    }
}
