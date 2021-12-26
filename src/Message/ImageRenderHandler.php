<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Message;

use Camelot\Arbitration\Responder\ResponderInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class ImageRenderHandler implements MessageHandlerInterface
{
    private ResponderInterface $responder;

    public function __construct(ResponderInterface $responder)
    {
        $this->responder = $responder;
    }

    public function __invoke(ImageRenderMessage $message): void
    {
        foreach ($message->getRenditions() as $rendition) {
            $this->responder->respond($message->getFileInfo(), $rendition);
        }
    }
}
