<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Controller;

use Camelot\Arbitration\Filesystem\FilesystemInterface;
use Camelot\Arbitration\Filesystem\Finder;
use Camelot\Arbitration\Responder\ResponderInterface;
use Camelot\Arbitration\ResponseFactory\ResponseFactoryInterface;

trait ControllerTrait
{
    private Finder $finder;
    private FilesystemInterface $renderFilesystem;
    private ResponderInterface $responder;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(Finder $finder, FilesystemInterface $renderFilesystem, ResponderInterface $responder, ResponseFactoryInterface $responseFactory)
    {
        $this->finder = $finder;
        $this->renderFilesystem = $renderFilesystem;
        $this->responder = $responder;
        $this->responseFactory = $responseFactory;
    }

    private function createResponse(object $request, string $path): mixed
    {
        $render = $this->renderFilesystem->getFileInfo($path);
        $source = $this->finder->getSourceFromRender($render);
        $response = $this->responseFactory->create($this->responder->respond($source, $this->finder->getRenditionNameFromRender($render)));
        $response->isNotModified($request);

        return $response;
    }
}
