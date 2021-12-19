<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Util;

use function is_numeric;
use function is_string;
use function preg_match;
use function preg_replace;
use function preg_split;

final class Ratio
{
    public static function normaliseOptions(?array $options): ?array
    {
        if (!$options) {
            return $options;
        }

        ['ratio' => $ratio, 'width' => $width, 'height' => $height] = $options + ['ratio' => null, 'width' => null, 'height' => null];

        if ($ratio && is_numeric($ratio)) {
            $ratio = (float) $ratio;
        } elseif ($ratio && preg_match('/[0-9]+[x:_\-][0-9]+/', $ratio)) {
            [$x, $y] = preg_split('/[x:_\-]/', $ratio);
            $ratio = (float) ($x / $y);
        }

        if ($width && is_string($width)) {
            $width = (int) preg_replace('/^(\d+)\w+/', '$1', $width);
        }

        if ($height && is_string($height)) {
            $height = (int) preg_replace('/^(\d+)\w+/', '$1', $height);
        }

        // Width takes priority if ratio is also set
        if ($ratio && $width) {
            $height = (int) ($width / $ratio);
        } elseif ($ratio && $height) {
            $width = (int) ($height * $ratio);
        }

        $options['ratio'] = $ratio;
        $options['width'] = $width;
        $options['height'] = $height;

        return $options;
    }
}
