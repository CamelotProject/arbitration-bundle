<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Manipulators;

use Intervention\Image\Image;

/**
 * @copyright Jonathan Reinink <jonathan@reinink.ca>
 *
 * @property null|string $contrast
 */
class Contrast extends BaseManipulator
{
    /**
     * Perform contrast image manipulation.
     *
     * @param Image $image the source image
     *
     * @return Image the manipulated image
     */
    public function run(Image $image): Image
    {
        $contrast = $this->getContrast();

        if ($contrast !== null) {
            $image->contrast($contrast);
        }

        return $image;
    }

    /**
     * Resolve contrast amount.
     *
     * @return null|int the resolved contrast amount
     */
    public function getContrast(): ?int
    {
        if ($this->contrast === null || !preg_match('/^-*[0-9]+$/', (string) $this->contrast)) {
            return null;
        }

        if ($this->contrast < -100 || $this->contrast > 100) {
            return null;
        }

        return (int) $this->contrast;
    }
}
