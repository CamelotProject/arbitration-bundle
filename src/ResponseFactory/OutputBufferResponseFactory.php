<?php

declare(strict_types=1);

namespace Camelot\Arbitration\ResponseFactory;

use Camelot\Arbitration\Responder\ImageResponse;
use function header;
use function ob_clean;
use function ob_end_flush;

/**
 * @codeCoverageIgnore
 */
class OutputBufferResponseFactory implements ResponseFactoryInterface
{
    use ResponseFactoryTrait;

    public function create(ImageResponse $imageResponse, ?object $response = null): mixed
    {
        ob_clean();
        ob_start();

        header('Content-Type:' . $imageResponse->getMimeType());
        header('Last-Modified:' . $imageResponse->getLastModified()->format('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control:' . "max-age={$this->getMaxAge()}, public");
        header('Expires:' . $this->getExpires()->format('D, d M Y H:i:s') . ' GMT');

        echo $imageResponse->getContent();

        ob_end_flush();

        return null;
    }
}
