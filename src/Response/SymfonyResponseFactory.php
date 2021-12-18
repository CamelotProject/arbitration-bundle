<?php

declare(strict_types=1);

namespace Camelot\Intervention\Response;

use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @copyright Jonathan Reinink <jonathan@reinink.ca>
 */
class SymfonyResponseFactory
{
    /** Request object to check "is not modified". */
    protected ?Request $request = null;

    /**
     * Create SymfonyResponseFactory instance.
     *
     * @param null|Request $request request object to check "is not modified"
     */
    public function __construct(Request $request = null)
    {
        $this->request = $request;
    }

    public function setRequest(Request $request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Create the response.
     *
     * @param FilesystemOperator $cache the cache file system
     * @param string             $path  the cached file path
     *
     * @return StreamedResponse the response object
     */
    public function create(FilesystemOperator $cache, string $path): Response
    {
        $stream = $cache->readStream($path);

        $response = new StreamedResponse();
        $response->headers->set('Content-Type', $cache->mimeType($path));
        $response->headers->set('Content-Length', (string) $cache->fileSize($path));
        $response->setPublic();
        $response->setMaxAge(31536000);
        $response->setExpires(date_create()->modify('+1 years'));

        if ($this->request) {
            $response->setLastModified(date_create()->setTimestamp($cache->lastModified($path)));
            $response->isNotModified($this->request);
        }

        $response->setCallback(function () use ($stream): void {
            // @codeCoverageIgnoreStart
            if (ftell($stream) !== 0) {
                rewind($stream);
            }
            fpassthru($stream);
            fclose($stream);
            // @codeCoverageIgnoreEnd
        });

        return $response;
    }
}
