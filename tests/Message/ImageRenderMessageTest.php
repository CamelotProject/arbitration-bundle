<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Message;

use Camelot\Arbitration\Configuration\Rendition;
use Camelot\Arbitration\Filesystem\FileInfo;
use Camelot\Arbitration\Message\ImageRenderMessage;
use Camelot\Arbitration\Tests\Fixtures\RenditionsFixture;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Arbitration\Message\ImageRenderMessage
 *
 * @internal
 */
final class ImageRenderMessageTest extends TestCase
{
    private FileInfo $fileInfo;
    /** @var Rendition[] */
    private array $renditions;

    protected function setUp(): void
    {
        $this->fileInfo = new FileInfo('/var/www/public/images/image.jpg', '/var/www/public');
        $this->renditions = RenditionsFixture::createFixture()->getSet('list_page')->getRenditions();
    }

    public function testGetFileInfo(): void
    {
        static::assertSame($this->fileInfo, $this->getImageRenderMessage()->getFileInfo());
    }

    public function testGetRenditions(): void
    {
        static::assertSame($this->renditions, $this->getImageRenderMessage()->getRenditions());
    }

    private function getImageRenderMessage(): ImageRenderMessage
    {
        return new ImageRenderMessage($this->fileInfo, $this->renditions);
    }
}
