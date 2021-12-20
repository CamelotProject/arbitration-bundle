<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Responder;

use BadMethodCallException;
use Camelot\Arbitration\Responder\ImageResponse;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Camelot\Arbitration\Responder\ImageResponse
 *
 * @internal
 */
final class ImageResponseTest extends TestCase
{
    public function testConstructorThrowsException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Parameter #1 of');

        new ImageResponse(new stdClass(), 'image/webp', new DateTimeImmutable());
    }

    public function providerIsEager(): iterable
    {
        yield [true, 'foo'];
        yield [false, fn() => 'foo'];
    }

    /** @dataProvider providerIsEager */
    public function testIsEager(bool $expected, string|callable $content): void
    {
        static::assertSame($expected, $this->getResponse($content)->isEager());
    }

    public function testGetContent(): void
    {
        static::assertSame('foo', $this->getResponse('foo')->getContent());
    }

    public function testGetContentCallable(): void
    {
        static::assertSame('foo', $this->getResponse(fn() => 'foo')->getContent()());
    }

    public function testGetMimeType(): void
    {
        static::assertSame('image/jpg', $this->getResponse(null, 'image/jpg')->getMimeType());
    }

    public function testGetLastModified(): void
    {
        $dateTime = new DateTimeImmutable();

        static::assertSame($dateTime, $this->getResponse(null, null, $dateTime)->getLastModified());
    }

    private function getResponse(null|string|callable $content = null, ?string $mimeType = null, ?DateTimeInterface $lastModified = null): ImageResponse
    {
        return new ImageResponse($content ?: '', $mimeType ?: '', $lastModified ?: new DateTimeImmutable());
    }
}
