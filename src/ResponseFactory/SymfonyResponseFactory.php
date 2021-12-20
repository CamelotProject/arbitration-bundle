<?php

declare(strict_types=1);

namespace Camelot\Arbitration\ResponseFactory;

use Camelot\Arbitration\Responder\ImageResponse;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function sprintf;

/**
 * @copyright Jonathan Reinink <jonathan@reinink.ca>
 */
class SymfonyResponseFactory implements ResponseFactoryInterface
{
    use ResponseFactoryTrait;

    public function create(ImageResponse $imageResponse, ?object $response = null): SymfonyResponse
    {
        if ($response && !$response instanceof SymfonyResponse) {
            throw new RuntimeException(sprintf('Argument #2 of %s requires a %s object, %s provided.', __METHOD__, SymfonyResponse::class, $response::class));
        }

        if (!$response) {
            $response = $imageResponse->isEager() ? new SymfonyResponse() : new StreamedResponse();
        }
        $response->headers->set('Content-Type', $imageResponse->getMimeType());
        $response->setLastModified($imageResponse->getLastModified());
        $response->setPublic();
        $response->setMaxAge($this->getMaxAge());
        $response->setExpires($this->getExpires());

        if ($response instanceof StreamedResponse) {
            $response->setCallback($imageResponse->getContent());
        } else {
            $response->setContent($imageResponse->getContent());
        }

        return $response;
    }
}
