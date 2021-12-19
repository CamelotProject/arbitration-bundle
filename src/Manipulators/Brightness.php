<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Manipulators;

use Intervention\Image\Image;

/**
 * @copyright Jonathan Reinink <jonathan@reinink.ca>
 *
 * @property null|string $brightness
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
    public function run(Image $image): Image
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
    public function getBrightness(): ?int
    {
        if ($this->brightness === null || !preg_match('/^-*[0-9]+$/', (string) $this->brightness)) {
            return null;
        }

        if ($this->brightness < -100 || $this->brightness > 100) {
            return null;
        }

        return (int) $this->brightness;
    }
}
