<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SymfonyImageController
{
    use ControllerTrait;

    public function __invoke(Request $request, string $path): Response
    {
        return $this->createResponse($request, $path);
    }
}
