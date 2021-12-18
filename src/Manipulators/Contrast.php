<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Manipulators;

use Intervention\Image\Image;

/**
 * @copyright Jonathan Reinink <jonathan@reinink.ca>
 *
 * @property null|string $con
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
    public function run(Image $image)
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
    public function getContrast()
    {
        if ($this->con === null || !preg_match('/^-*[0-9]+$/', $this->con)) {
            return;
        }

        if ($this->con < -100 || $this->con > 100) {
            return;
        }

        return (int) $this->con;
    }
}
