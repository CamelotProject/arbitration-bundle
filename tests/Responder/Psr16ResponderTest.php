<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Responder;

use Camelot\Arbitration\Api\InterveneInterface;
use Camelot\Arbitration\Filesystem\FileInfo;
use Camelot\Arbitration\Generator\PathnameGenerator;
use Camelot\Arbitration\Generator\PathnameGeneratorInterface;
use Camelot\Arbitration\Responder\ImageResponse;
use Camelot\Arbitration\Responder\Psr16Responder;
use Camelot\Arbitration\Tests\Fixtures\RenditionsFixture;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use function iterator_to_array;

/**
 * @covers \Camelot\Arbitration\Responder\Psr16Responder
 *
 * @internal
 */
final class Psr16ResponderTest extends TestCase
{
    private InterveneInterface $intervene;
    private CacheInterface $cache;
    private PathnameGeneratorInterface $pathGenerator;

    protected function setUp(): void
    {
        $this->intervene = $this->createMock(InterveneInterface::class);
        $this->cache = new Psr16Cache(new ArrayAdapter());
        $this->pathGenerator = new PathnameGenerator();
    }

    public function providerRespond(): iterable
    {
        yield 'Uncached' => [false];
        yield 'Cached' => [true];
    }

    /** @dataProvider providerRespond */
    public function testRespond(bool $isCached): void
    {
        $source = new FileInfo(__DIR__ . '/../Fixtures/images/image.webp', __DIR__);

        $key = $this->pathGenerator->generate($source, RenditionsFixture::createFixture()->get('1920x1080'));

        if ($isCached) {
            $this->cache->set($key, 'handled goods');
        } else {
            $this->intervene
                ->expects(static::once())
                ->method('handle')
                ->willReturn('handled goods')
            ;
        }

        static::assertSame('handled goods', $this->getResponder()->respond($source, '1920x1080')->getContent());
    }

    public function providerRespondBatch(): iterable
    {
        yield 'Uncached' => [false];
        yield 'Cached' => [true];
    }

    /** @dataProvider providerRespondBatch */
    public function testRespondBatch(bool $isCached): void
    {
        $source = new FileInfo(__DIR__ . '/../Fixtures/images/image.webp', __DIR__);
        $item1 = 'handled goods 150x100';
        $item2 = 'handled goods 150x100';

        if ($isCached) {
            $renditions = RenditionsFixture::createFixture();
            $this->cache->set($this->pathGenerator->generate($source, $renditions->get('150x100')), $item1);
            $this->cache->set($this->pathGenerator->generate($source, $renditions->get('150x100')), $item2);
        } else {
            $this->intervene
                ->expects(static::atLeast(2))
                ->method('handle')
                ->withAnyParameters()
                ->willReturnOnConsecutiveCalls('handled goods 150x100', 'handled goods 150x100')
            ;
        }

        $responses = iterator_to_array($this->getResponder()->respondBatch($source, 'list_page'));

        static::assertInstanceOf(ImageResponse::class, $responses['150x100']);
        static::assertInstanceOf(ImageResponse::class, $responses['150x100']);

        static::assertSame($item1, $responses['150x100']->getContent());
        static::assertSame($item2, $responses['150x100']->getContent());
    }

    private function getResponder(): Psr16Responder
    {
        return new Psr16Responder($this->intervene, RenditionsFixture::createFixture(), $this->pathGenerator, $this->cache);
    }
}
