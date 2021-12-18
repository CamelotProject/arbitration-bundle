<?php

declare(strict_types=1);

namespace Camelot\Intervention\Tests\Response;

use Camelot\Intervention\Response\SymfonyResponseFactory;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \Camelot\Intervention\Response\SymfonyResponseFactory
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

    public function testCreate(): void
    {
        $cache = Mockery::mock('League\Flysystem\FilesystemOperator', function ($mock): void {
            $mock->shouldReceive('mimeType')->andReturn('image/jpeg')->once();
            $mock->shouldReceive('fileSize')->andReturn(0)->once();
            $mock->shouldReceive('readStream');
        });

        $factory = new SymfonyResponseFactory();
        $response = $factory->create($cache, '');

        static::assertInstanceOf('Symfony\Component\HttpFoundation\StreamedResponse', $response);
        static::assertSame('image/jpeg', $response->headers->get('Content-Type'));
        static::assertSame('0', $response->headers->get('Content-Length'));
        static::assertStringContainsString(gmdate('D, d M Y H:i', strtotime('+1 years')), $response->headers->get('Expires'));
        static::assertSame('max-age=31536000, public', $response->headers->get('Cache-Control'));
    }
}
