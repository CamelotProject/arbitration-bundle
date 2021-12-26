<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Generator;

use Camelot\Arbitration\Generator\PathnameGenerator;
use Camelot\Arbitration\Generator\SourceSetGenerator;
use Camelot\Arbitration\Tests\Fixtures\RenditionsFixture;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Arbitration\Generator\SourceSetGenerator
 *
 * @internal
 */
final class SourceSetGeneratorTest extends TestCase
{
    public function testGenerate(): void
    {
        $expected = [
            '300x200' => [
                'width' => 300,
                'height' => 200,
                'uri' => 'render/300x200/jpg/foo/bar/image.png',
            ],
            '150x100' => [
                'width' => 150,
                'height' => 100,
                'uri' => 'render/150x100/jpg/foo/bar/image.png',
            ],
        ];
        $set = $this->getSourceSet()->generate('foo/bar/image.jpg', 'list_page');

        static::assertSame($expected, $set);
    }

    public function testGenerateInvalidPathname(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Pathname missing');

        $this->getSourceSet()->generate('', 'list_page');
    }

    private function getSourceSet(): SourceSetGenerator
    {
        return new SourceSetGenerator(RenditionsFixture::createFixture(), new PathnameGenerator(), '/tmp', '/tmp/render');
    }
}
