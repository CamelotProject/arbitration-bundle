<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Manipulators;

use Intervention\Image\Image;

/**
 * @copyright Jonathan Reinink <jonathan@reinink.ca>
 *
 * @property string $flip
 */
class Flip extends BaseManipulator
{
    /**
     * Perform flip image manipulation.
     *
     * @param Image $image the source image
     *
     * @return Image the manipulated image
     */
    public function run(Image $image)
    {
        if ($flip = $this->getFlip()) {
            if ($flip === 'both') {
                return $image->flip('h')->flip('v');
            }

            return $image->flip($flip);
        }

        return $image;
    }

    /**
     * Resolve flip.
     *
     * @return null|string the resolved flip
     */
    public function getFlip()
    {
        if (\in_array($this->flip, ['h', 'v', 'both'], true)) {
            return $this->flip;
        }
    }
}
