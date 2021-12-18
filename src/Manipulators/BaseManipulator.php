<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Manipulators;

use Intervention\Image\Image;
use function array_key_exists;

/**
 * @copyright Jonathan Reinink <jonathan@reinink.ca>
 */
abstract class BaseManipulator implements ManipulatorInterface
{
    /** The manipulation params. */
    public array $params = [];

    /**
     * Get a specific manipulation param.
     *
     * @param string $name the manipulation name
     *
     * @return string the manipulation value
     */
    public function __get(string $name): null|int|float|string
    {
        if (array_key_exists($name, $this->params)) {
            return $this->params[$name];
        }

        return null;
    }

    /**
     * Set the manipulation params.
     *
     * @param array $params the manipulation params
     */
    public function setParams(array $params): ManipulatorInterface
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Perform the image manipulation.
     *
     * @return Image the manipulated image
     */
    abstract public function run(Image $image): Image;
}
