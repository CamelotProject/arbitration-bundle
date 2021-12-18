<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Manipulators;

use Intervention\Image\Image;

/**
 * @copyright Jonathan Reinink <jonathan@reinink.ca>
 */
abstract class BaseManipulator implements ManipulatorInterface
{
    /**
     * The manipulation params.
     *
     * @var array
     */
    public $params = [];

    /**
     * Get a specific manipulation param.
     *
     * @param string $name the manipulation name
     *
     * @return string the manipulation value
     */
    public function __get($name)
    {
        if (\array_key_exists($name, $this->params)) {
            return $this->params[$name];
        }
    }

    /**
     * Set the manipulation params.
     *
     * @param array $params the manipulation params
     *
     * @return $this
     */
    public function setParams(array $params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Perform the image manipulation.
     *
     * @return Image the manipulated image
     */
    abstract public function run(Image $image);
}
