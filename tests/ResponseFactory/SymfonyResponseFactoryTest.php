<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\ResponseFactory;

use Camelot\Arbitration\Responder\ImageResponse;
use Camelot\Arbitration\ResponseFactory\SymfonyResponseFactory;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @covers \Camelot\Arbitration\ResponseFactory\SymfonyResponseFactory
 * @covers \Camelot\Arbitration\ResponseFactory\ResponseFactoryTrait
 *
 * @internal
 */
final class SymfonyResponseFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $factory = new SymfonyResponseFactory(1024);
        $response = $factory->create(new ImageResponse('image data', 'mime/type', new DateTimeImmutable()), null);

        static::assertSame(Response::class, $response::class);
        static::assertSame('image data', $response->getContent());
    }

    public function testCreateStreamed(): void
    {
        $called = null;
        $factory = new SymfonyResponseFactory(1024);
        $response = $factory->create(new ImageResponse(function () use (&$called) { return $called = 'image data'; }, 'mime/type', new DateTimeImmutable()), null);

        static::assertInstanceOf(StreamedResponse::class, $response);

        $response->sendContent();

        static::assertSame('image data', $called);
    }

    public function testCreateInvalidResponseObject(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Argument #2 of %s::create requires a %s object, stdClass provided.', SymfonyResponseFactory::class, Response::class));

        $factory = new SymfonyResponseFactory(1024);
        $factory->create(new ImageResponse('image data', 'mime/type', new DateTimeImmutable()), new \stdClass());
    }
}
