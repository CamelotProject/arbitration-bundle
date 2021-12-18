<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Manipulators;

use Intervention\Image\Image;

/**
 * @copyright Jonathan Reinink <jonathan@reinink.ca>
 */
interface ManipulatorInterface
{
    /**
     * Set the manipulation params.
     *
     * @param array $params the manipulation params
     */
    public function setParams(array $params): self;

    /**
     * Perform the image manipulation.
     *
     * @param Image $image the source image
     *
     * @return Image the manipulated image
     */
    public function run(Image $image): Image;
}
