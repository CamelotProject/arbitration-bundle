<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Manipulators;

use Intervention\Image\Image;

/**
 * @copyright Jonathan Reinink <jonathan@reinink.ca>
 *
 * @property null|string $gamma
 */
class Gamma extends BaseManipulator
{
    /**
     * Perform gamma image manipulation.
     *
     * @param Image $image the source image
     *
     * @return Image the manipulated image
     */
    public function run(Image $image): Image
    {
        $gamma = $this->getGamma();

        if ($gamma) {
            $image->gamma($gamma);
        }

        return $image;
    }

    /**
     * Resolve gamma amount.
     *
     * @return null|float the resolved gamma amount
     */
    public function getGamma(): ?float
    {
        if ($this->gamma === null || !preg_match('/^[0-9]\.*[0-9]*$/', (string) $this->gamma)) {
            return null;
        }

        if ($this->gamma < 0.1 || $this->gamma > 9.99) {
            return null;
        }

        return (float) $this->gamma;
    }
}
