<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Manipulators;

use Intervention\Image\Image;
use function array_key_exists;
use function in_array;

/**
 * @copyright Jonathan Reinink <jonathan@reinink.ca>
 *
 * @property string $format
 * @property string $quality
 */
class Encode extends BaseManipulator
{
    /**
     * Perform output image manipulation.
     *
     * @param Image $image the source image
     *
     * @return Image the manipulated image
     */
    public function run(Image $image): Image
    {
        $format = $this->getFormat($image);
        $quality = $this->getQuality();

        if (in_array($format, ['jpg', 'pjpg'], true)) {
            $image = $image->getDriver()
                ->newImage($image->width(), $image->height(), '#fff')
                ->insert($image, 'top-left', 0, 0)
            ;
        }

        if ($format === 'pjpg') {
            $image->interlace();
            $format = 'jpg';
        }

        return $image->encode($format, $quality);
    }

    /**
     * Resolve format.
     *
     * @param Image $image the source image
     *
     * @return string the resolved format
     */
    public function getFormat(Image $image): string
    {
        $allowed = [
            'avif' => 'image/avif',
            'gif' => 'image/gif',
            'jpg' => 'image/jpeg',
            'pjpg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
        ];

        if (array_key_exists($this->format, $allowed)) {
            return $this->format;
        }

        if ($format = array_search($image->mime(), $allowed, true)) {
            return $format;
        }

        return 'jpg';
    }

    /**
     * Resolve quality.
     *
     * @return int the resolved quality
     */
    public function getQuality(): int
    {
        $default = 90;

        if (!is_numeric($this->quality)) {
            return $default;
        }

        if ($this->quality < 0 || $this->quality > 100) {
            return $default;
        }

        return (int) $this->quality;
    }
}
