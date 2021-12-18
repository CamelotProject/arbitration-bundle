<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Manipulators;

use Camelot\Arbitration\Manipulators\Helpers\Color;
use Intervention\Image\Image;

/**
 * @copyright Jonathan Reinink <jonathan@reinink.ca>
 *
 * @property string $bg
 */
class Background extends BaseManipulator
{
    /**
     * Perform background image manipulation.
     *
     * @param Image $image the source image
     *
     * @return Image the manipulated image
     */
    public function run(Image $image)
    {
        if ($this->bg === null) {
            return $image;
        }

        $color = (new Color($this->bg))->formatted();

        if ($color) {
            $new = $image->getDriver()->newImage($image->width(), $image->height(), $color);
            $new->mime = $image->mime;
            $image = $new->insert($image, 'top-left', 0, 0);
        }

        return $image;
    }
}
