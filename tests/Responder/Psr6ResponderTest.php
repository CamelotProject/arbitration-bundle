<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Responder;

use Camelot\Arbitration\Api\InterveneInterface;
use Camelot\Arbitration\Filesystem\FileInfo;
use Camelot\Arbitration\Generator\PathnameGenerator;
use Camelot\Arbitration\Generator\PathnameGeneratorInterface;
use Camelot\Arbitration\Responder\ImageResponse;
use Camelot\Arbitration\Responder\Psr6Responder;
use Camelot\Arbitration\Tests\Fixtures\RenditionsFixture;
use DateTime;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use function iterator_to_array;

/**
 * @covers \Camelot\Arbitration\Responder\Psr6Responder
 *
 * @internal
 */
final class Psr6ResponderTest extends TestCase
{
    private InterveneInterface $intervene;
    private CacheItemPoolInterface $cache;
    private PathnameGeneratorInterface $pathGenerator;

    protected function setUp(): void
    {
        $this->intervene = $this->createMock(InterveneInterface::class);
        $this->cache = new ArrayAdapter();
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
        $item = $this->cache->getItem($this->pathGenerator->generate($source, RenditionsFixture::createFixture()->get('1920x1080')));
        $item->set('handled goods');

        if ($isCached === false) {
            $item->expiresAt(new DateTime('now'));
            $this->intervene
                ->expects(static::once())
                ->method('handle')
                ->willReturn('handled goods')
            ;
        }

        $this->cache->save($item);

        static::assertSame($item->get(), $this->getResponder()->respond($source, '1920x1080')->getContent());
    }

    public function providerRespondBatch(): iterable
    {
        yield 'Uncached' => [false];
        yield 'Cached' => [true];
    }

    /** @dataProvider providerRespondBatch */
    public function testRespondBatch(bool $isCached): void
    {
        $renditions = RenditionsFixture::createFixture();
        $source = new FileInfo(__DIR__ . '/../Fixtures/images/image.webp', __DIR__);
        $item1 = $this->cache->getItem($this->pathGenerator->generate($source, $renditions->get('150x100')));
        $item1->set('handled goods 150x100');
        $item2 = $this->cache->getItem($this->pathGenerator->generate($source, $renditions->get('150x100')));
        $item2->set('handled goods 150x100');

        if ($isCached === false) {
            $item1->expiresAt(new DateTime('now'));
            $item2->expiresAt(new DateTime('now'));

            $this->intervene
                ->expects(static::atLeast(2))
                ->method('handle')
                ->withAnyParameters()
                ->willReturnOnConsecutiveCalls('handled goods 150x100', 'handled goods 150x100')
            ;
        }
        $this->cache->save($item1);
        $this->cache->save($item2);

        $responses = iterator_to_array($this->getResponder()->respondBatch($source, 'list_page'));

        static::assertInstanceOf(ImageResponse::class, $responses['150x100']);
        static::assertInstanceOf(ImageResponse::class, $responses['150x100']);

        static::assertSame($item1->get(), $responses['150x100']->getContent());
        static::assertSame($item2->get(), $responses['150x100']->getContent());
    }

    private function getResponder(): Psr6Responder
    {
        return new Psr6Responder($this->intervene, RenditionsFixture::createFixture(), $this->pathGenerator, $this->cache);
    }
}
