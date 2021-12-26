<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Message;

use Camelot\Arbitration\Filesystem\FileInfo;
use Camelot\Arbitration\Message\ImageRenderHandler;
use Camelot\Arbitration\Message\ImageRenderMessage;
use Camelot\Arbitration\Responder\ImageResponse;
use Camelot\Arbitration\Responder\ResponderInterface;
use Camelot\Arbitration\Tests\Fixtures\RenditionsFixture;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Arbitration\Message\ImageRenderHandler
 *
 * @internal
 */
final class ImageRenderHandlerTest extends TestCase
{
    private ResponderInterface $responder;

    protected function setUp(): void
    {
        $this->responder = $this->createMock(ResponderInterface::class);
    }

    public function testInvoke(): void
    {
        $message = new ImageRenderMessage(new FileInfo('/var/www/pubic/image.png', '/var/www/pubic'), RenditionsFixture::createFixture()->getSet('list_page')->getRenditions());
        $this->responder
            ->expects(static::atLeast(2))
            ->method('respond')
            ->willReturn(new ImageResponse('image data', 'image/unknown', new DateTimeImmutable()))
        ;

        ($this->getHandler())($message);
    }

    private function getHandler(): ImageRenderHandler
    {
        return new ImageRenderHandler($this->responder);
    }
}
