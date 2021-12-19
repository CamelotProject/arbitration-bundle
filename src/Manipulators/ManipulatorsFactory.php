<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Manipulators;

use Camelot\Arbitration\Manipulators;

final class ManipulatorsFactory
{
    public static function create(): array
    {
        return [
            new Manipulators\Orientation(),
            new Manipulators\Crop(),
            new Manipulators\Size(),
            new Manipulators\Brightness(),
            new Manipulators\Contrast(),
            new Manipulators\Gamma(),
            new Manipulators\Sharpen(),
            new Manipulators\Filter(),
            new Manipulators\Blur(),
            new Manipulators\Pixelate(),
            new Manipulators\Watermark(),
            new Manipulators\Background(),
            new Manipulators\Border(),
            new Manipulators\Encode(),
        ];
    }
}
