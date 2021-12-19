<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Manipulators;

use Intervention\Image\Image;
use function in_array;
use function is_numeric;

/**
 * @copyright Jonathan Reinink <jonathan@reinink.ca>
 *
 * @property int|string $orientation
 */
class Orientation extends BaseManipulator
{
    /**
     * Perform orientation image manipulation.
     *
     * @param Image $image the source image
     *
     * @return Image the manipulated image
     */
    public function run(Image $image): Image
    {
        $orientation = $this->getOrientation();

        if ($orientation === 'auto') {
            return $image->orientate();
        }

        return $image->rotate((float) $orientation);
    }

    /**
     * Resolve orientation.
     *
     * @return int|string the resolved orientation
     */
    public function getOrientation(): string|int
    {
        if (is_numeric($this->orientation) && in_array((int) $this->orientation, [0, 90, 180, 270], true)) {
            return (int) $this->orientation;
        }

        return 'auto';
    }
}
