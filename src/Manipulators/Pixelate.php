<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Manipulators;

use Intervention\Image\Image;

/**
 * @copyright Jonathan Reinink <jonathan@reinink.ca>
 *
 * @property string $pixel
 */
class Pixelate extends BaseManipulator
{
    /**
     * Perform pixelate image manipulation.
     *
     * @param Image $image the source image
     *
     * @return Image the manipulated image
     */
    public function run(Image $image): Image
    {
        $pixelate = $this->getPixelate();

        if ($pixelate !== null) {
            $image->pixelate($pixelate);
        }

        return $image;
    }

    /**
     * Resolve pixelate amount.
     *
     * @return null|int the resolved pixelate amount
     */
    public function getPixelate(): ?int
    {
        if (!is_numeric($this->pixel)) {
            return null;
        }

        if ($this->pixel < 0 || $this->pixel > 1000) {
            return null;
        }

        return (int) $this->pixel;
    }
}
