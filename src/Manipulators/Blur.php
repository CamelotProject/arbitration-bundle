<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Manipulators;

use Intervention\Image\Image;

/**
 * @copyright Jonathan Reinink <jonathan@reinink.ca>
 *
 * @property string $blur
 */
class Blur extends BaseManipulator
{
    /**
     * Perform blur image manipulation.
     *
     * @param Image $image the source image
     *
     * @return Image the manipulated image
     */
    public function run(Image $image): Image
    {
        $blur = $this->getBlur();

        if ($blur !== null) {
            $image->blur($blur);
        }

        return $image;
    }

    /**
     * Resolve blur amount.
     *
     * @return null|int the resolved blur amount
     */
    public function getBlur(): ?int
    {
        if (!is_numeric($this->blur)) {
            return null;
        }

        if ($this->blur < 0 || $this->blur > 100) {
            return null;
        }

        return (int) $this->blur;
    }
}
