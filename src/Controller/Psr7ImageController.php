<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class Psr7ImageController
{
    use ControllerTrait;

    public function __invoke(RequestInterface $request, string $path): ResponseInterface
    {
        return $this->createResponse($request, $path);
    }
}
