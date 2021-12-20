<?php

declare(strict_types=1);

namespace Camelot\Arbitration\ResponseFactory;

use Camelot\Arbitration\Responder\ImageResponse;
use Psr\Http\Message\ResponseInterface;

class Psr7ResponseFactory implements ResponseFactoryInterface
{
    use ResponseFactoryTrait;

    /** @param null|ResponseInterface $response */
    public function create(ImageResponse $imageResponse, ?object $response = null): ResponseInterface
    {
        return $response
            ->withBody($imageResponse->getContent())
            ->withHeader('Content-Type', $imageResponse->getMimeType())
            ->withHeader('Last-Modified', $imageResponse->getLastModified()->format('D, d M Y H:i:s') . ' GMT')
            ->withHeader('Cache-Control', "max-age={$this->getMaxAge()}}, public")
            ->withHeader('Expires', $this->getExpires()->format('D, d M Y H:i:s') . ' GMT')
        ;
    }
}
