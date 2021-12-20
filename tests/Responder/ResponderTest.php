<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Responder;

use Camelot\Arbitration\Api\Intervene;
use Camelot\Arbitration\Filesystem\FileInfo;
use Camelot\Arbitration\Responder\Responder;
use Camelot\Arbitration\Tests\Fixtures\RenditionsFixture;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Arbitration\Responder\Responder
 * @covers \Camelot\Arbitration\Responder\ResponderTrait
 *
 * @internal
 */
final class ResponderTest extends TestCase
{
    private ImageManager $imageManager;

    protected function setUp(): void
    {
        $this->imageManager = $this->createMock(ImageManager::class);
    }

    public function testRespond(): void
    {
        $this->setMockExpectations(static::once(), static::once());

        $this->getResponder()->respond($this->getSource(), '1920x1080');
    }

    public function testRespondPassingRendition(): void
    {
        $this->setMockExpectations(static::once(), static::once());

        $this->getResponder()->respond($this->getSource(), RenditionsFixture::createFixture()->get('150x100'));
    }

    public function testRespondBatch(): void
    {
        $this->setMockExpectations(static::atLeast(2), static::atLeast(2));

        $result = $this->getResponder()->respondBatch($this->getSource(), 'list_page');

        static::assertIsIterable($result);
        static::assertCount(2, $result);
    }

    private function setMockExpectations(InvocationOrder $getEncoded, InvocationOrder $make): void
    {
        $image = $this->getMockBuilder(Image::class)
            ->allowMockingUnknownTypes()
            ->addMethods(['orientate', 'resize'])
            ->onlyMethods(['getEncoded', 'encode', 'width', 'height'])
            ->getMock()
        ;
        $image
            ->expects(static::any())
            ->method('encode')
            ->willReturnSelf()
        ;
        $image
            ->expects(static::any())
            ->method('orientate')
            ->willReturnSelf()
        ;
        $image
            ->expects(static::any())
            ->method('resize')
            ->willReturnSelf()
        ;
        $image
            ->expects($getEncoded)
            ->method('getEncoded')
            ->willReturn('image-data')
        ;
        $this->imageManager
            ->expects($make)
            ->method('make')
            ->willReturn($image)
        ;
    }

    private function getSource(): FileInfo
    {
        return new FileInfo(__DIR__ . '/../Fixtures/images/image.webp', __DIR__ . '/../Fixtures');
    }

    private function getResponder(): Responder
    {
        return new Responder(new Intervene($this->imageManager, []), RenditionsFixture::createFixture());
    }
}
