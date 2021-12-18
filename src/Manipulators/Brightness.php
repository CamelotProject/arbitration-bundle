<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Manipulators;

use Intervention\Image\Image;

/**
 * @copyright Jonathan Reinink <jonathan@reinink.ca>
 *
 * @property null|string $bri
 */
class Brightness extends BaseManipulator
{
    /**
     * Perform brightness image manipulation.
     *
     * @param Image $image the source image
     *
     * @return Image the manipulated image
     */
    public function run(Image $image)
    {
        $brightness = $this->getBrightness();

        if ($brightness !== null) {
            $image->brightness($brightness);
        }

        return $image;
    }

    /**
     * Resolve brightness amount.
     *
     * @return null|int the resolved brightness amount
     */
    public function getBrightness()
    {
        if ($this->bri === null || !preg_match('/^-*[0-9]+$/', $this->bri)) {
            return;
        }

        if ($this->bri < -100 || $this->bri > 100) {
            return;
        }

        return (int) $this->bri;
    }
}
