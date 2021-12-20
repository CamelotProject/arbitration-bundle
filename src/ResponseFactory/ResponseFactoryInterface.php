<?php

declare(strict_types=1);

namespace Camelot\Arbitration\ResponseFactory;

use Camelot\Arbitration\Responder\ImageResponse;

interface ResponseFactoryInterface
{
    /**
     * Create a client response.
     *
     * @param ImageResponse $imageResponse response from a responder
     * @param null|object       $response          implementation specific response object
     *
     * @return mixed implementation specific response
     */
    public function create(ImageResponse $imageResponse, ?object $response = null): mixed;
}
