<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Response;

use Camelot\Arbitration\Response\SymfonyResponseFactory;
use DateTimeImmutable;
use League\Flysystem\FilesystemOperator;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function gmdate;
use function strtotime;

/**
 * @internal
 * @covers \Camelot\Arbitration\Response\SymfonyResponseFactory
 */
final class SymfonyResponseFactoryTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateInstance(): void
    {
        static::assertInstanceOf(SymfonyResponseFactory::class, new SymfonyResponseFactory());
    }

    public function testCreateReturnsStreamedResponse(): void
    {
        static::assertInstanceOf(StreamedResponse::class, $this->createResponse());
    }

    public function testCreateResponseContentLength(): void
    {
        static::assertSame('1024', $this->createResponse()->headers->get('Content-Length'));
    }

    public function testCreateResponseExpiry(): void
    {
        static::assertStringContainsString(gmdate('D, d M Y H:i', strtotime('+1 years')), $this->createResponse()->headers->get('Expires'));
    }

    public function testCreateResponseCacheControl(): void
    {
        static::assertSame('max-age=31536000, public', $this->createResponse()->headers->get('Cache-Control'));
    }

    public function testCreateResponseLastModifiedWithoutRequest(): void
    {
        static::assertNull($this->createResponse()->getLastModified());
    }

    public function testCreateResponseLastModifiedWithRequest(): void
    {
        static::assertSame('2021-12-19 15:16:25+0000', $this->createResponse(new Request())->getLastModified()->format('Y-m-d H:i:sO'));
    }

    public function testCreateResponseLastModifiedWithRequestSetter(): void
    {
        static::assertSame('2021-12-19 15:16:25+0000', $this->createResponse(new Request())->getLastModified()->format('Y-m-d H:i:sO'));
    }

    private function createResponse(Request $request = null): Response
    {
        $cache = Mockery::mock(FilesystemOperator::class, function ($mock): void {
            $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s O', '2021-12-19 15:16:25+0000');

            $mock->shouldReceive('mimeType')->andReturn('image/jpeg')->once();
            $mock->shouldReceive('fileSize')->andReturn(1024)->once();
            $mock->shouldReceive('readStream');
            $mock->shouldReceive('lastModified')->with('image.jpg')->andReturn($date->getTimestamp());
        });
        $factory = new SymfonyResponseFactory($request);
        if ($request) {
            $factory->setRequest($request);
        }

        return $factory->create($cache, 'image.jpg');
    }
}
