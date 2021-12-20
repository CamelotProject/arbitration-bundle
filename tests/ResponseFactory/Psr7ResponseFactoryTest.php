<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\ResponseFactory;

use Camelot\Arbitration\Responder\ImageResponse;
use Camelot\Arbitration\ResponseFactory\Psr7ResponseFactory;
use DateTimeImmutable;
use Error;
use GuzzleHttp\Psr7;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use stdClass;

/**
 * @covers \Camelot\Arbitration\ResponseFactory\Psr7ResponseFactory
 *
 * @internal
 */
final class Psr7ResponseFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $factory = new Psr7ResponseFactory(1024);
        $stream = Psr7\Utils::streamFor('image data');
        $response = $factory->create(new ImageResponse($stream, 'mime/type', new DateTimeImmutable()), new Psr7\Response());

        static::assertInstanceOf(ResponseInterface::class, $response);
        static::assertSame('image data', $response->getBody()->getContents());
    }

    public function testCreateStreamed(): void
    {
        $called = null;
        $factory = new Psr7ResponseFactory(1024);
        $stream = Psr7\Utils::streamFor(function () use (&$called) { return $called = 'image data'; });
        $response = $factory->create(new ImageResponse($stream, 'mime/type', new DateTimeImmutable()), new Psr7\Response());
        $response->getBody()->read(1);

        static::assertSame('image data', $called);
    }

    public function testCreateInvalidResponseObject(): void
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessage('Call to undefined method stdClass::withBody');

        $factory = new Psr7ResponseFactory(1024);
        $factory->create(new ImageResponse('image data', 'mime/type', new DateTimeImmutable()), new stdClass());
    }
}
