<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Manipulators;

use Intervention\Image\Image;

/**
 * @copyright Jonathan Reinink <jonathan@reinink.ca>
 *
 * @property null|string $gam
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
    public function run(Image $image)
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
    public function getGamma()
    {
        if ($this->gam === null || !preg_match('/^[0-9]\.*[0-9]*$/', $this->gam)) {
            return;
        }

        if ($this->gam < 0.1 || $this->gam > 9.99) {
            return;
        }

        return (float) $this->gam;
    }
}
