<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Configuration;

use Camelot\Arbitration\Exception\InvalidRenditionException;
use Camelot\Arbitration\Exception\InvalidRenditionSetException;
use Camelot\Arbitration\Tests\Fixtures\RenditionsFixture;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Arbitration\Configuration\Renditions
 *
 * @internal
 */
final class RenditionsTest extends TestCase
{
    public function testList(): void
    {
        $renditions = RenditionsFixture::createFixture();

        static::assertSame(['1920x1080', '1024x768', '300x200', '150x100'], $renditions->list());
    }

    public function testHas(): void
    {
        $renditions = RenditionsFixture::createFixture();

        static::assertTrue($renditions->has('1920x1080'));
        static::assertNotTrue($renditions->has('1x1'));
    }

    public function testGet(): void
    {
        $renditions = RenditionsFixture::createFixture();
        $rendition = $renditions->get('1920x1080');

        static::assertSame(1920, $rendition->get('width'));
    }

    public function testGetInvalid(): void
    {
        $this->expectException(InvalidRenditionException::class);
        $this->expectExceptionMessage('No rendition named "invalid" exists. Available names:');

        $renditions = RenditionsFixture::createFixture();
        $renditions->get('invalid');
    }

    public function testListSets(): void
    {
        $renditions = RenditionsFixture::createFixture();

        static::assertSame(['list_page', 'page'], $renditions->listSets());
    }

    public function testHasSet(): void
    {
        $renditions = RenditionsFixture::createFixture();

        static::assertTrue($renditions->hasSet('list_page'));
        static::assertNotTrue($renditions->hasSet('nadda'));
    }

    public function testGetSet(): void
    {
        $renditions = RenditionsFixture::createFixture();
        $set = $renditions->getSet('list_page');

        static::assertSame('300x200', $set->getRenditions()['300x200']->getName());
        static::assertSame('150x100', $set->getRenditions()['150x100']->getName());
    }

    public function testGetSetInvalid(): void
    {
        $this->expectException(InvalidRenditionSetException::class);
        $this->expectExceptionMessage('No set named "invalid" exists. Available names:');

        $renditions = RenditionsFixture::createFixture();
        $renditions->getSet('invalid');
    }
}
