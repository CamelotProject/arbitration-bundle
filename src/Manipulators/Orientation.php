<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Manipulators;

use Intervention\Image\Image;
use function in_array;

/**
 * @copyright Jonathan Reinink <jonathan@reinink.ca>
 *
 * @property string $orientation
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
     * @return string the resolved orientation
     */
    public function getOrientation(): string
    {
        if (in_array($this->orientation, ['auto', '0', '90', '180', '270'], true)) {
            return $this->orientation;
        }

        return 'auto';
    }
}
