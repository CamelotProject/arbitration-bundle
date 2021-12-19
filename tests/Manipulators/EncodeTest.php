<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Manipulators\Encode;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Arbitration\Manipulators\BaseManipulator
 * @covers \Camelot\Arbitration\Manipulators\Encode
 *
 * @internal
 */
final class EncodeTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateInstance(): void
    {
        static::assertInstanceOf(Encode::class, new Encode());
    }

    public function providerRun(): iterable
    {
        $manager = new ImageManager();
        $jpg = $manager->canvas(100, 100)->encode('jpg');
        $png = $manager->canvas(100, 100)->encode('png');
        $gif = $manager->canvas(100, 100)->encode('gif');

        yield ['image/jpeg', ['format' => 'jpg'], $jpg];
        yield ['image/jpeg', ['format' => 'jpg'], $png];
        yield ['image/jpeg', ['format' => 'jpg'], $gif];
        yield ['image/jpeg', ['format' => 'pjpg'], $jpg];
        yield ['image/jpeg', ['format' => 'pjpg'], $png];
        yield ['image/jpeg', ['format' => 'pjpg'], $gif];
        yield ['image/png', ['format' => 'png'], $jpg];
        yield ['image/png', ['format' => 'png'], $png];
        yield ['image/png', ['format' => 'png'], $gif];
        yield ['image/gif', ['format' => 'gif'], $jpg];
        yield ['image/gif', ['format' => 'gif'], $png];
        yield ['image/gif', ['format' => 'gif'], $gif];
    }

    /** @dataProvider providerRun */
    public function testRun(string $expected, array $params, Image $image): void
    {
        static::assertSame($expected, (new Encode())->setParams($params)->run($image)->mime);
    }

    public function providerRunFromWebp(): iterable
    {
        $manager = new ImageManager();
        $jpg = $manager->canvas(100, 100)->encode('jpg');
        $png = $manager->canvas(100, 100)->encode('png');
        $gif = $manager->canvas(100, 100)->encode('gif');

        if (\function_exists('imagecreatefromwebp')) {
            $webp = $manager->canvas(100, 100)->encode('webp');
        }

        yield ['image/jpeg', ['format' => 'jpg'], $webp];
        yield ['image/jpeg', ['format' => 'pjpg'], $webp];
        yield ['image/png', ['format' => 'png'], $webp];
        yield ['image/gif', ['format' => 'gif'], $webp];
        yield ['image/webp', ['format' => 'webp'], $jpg];
        yield ['image/webp', ['format' => 'webp'], $png];
        yield ['image/webp', ['format' => 'webp'], $gif];
        yield ['image/webp', ['format' => 'webp'], $webp];
    }

    /**
     * @requires function imagecreatefromwebp
     * @dataProvider providerRunFromWebp
     */
    public function testRunFromWebp(string $expected, array $params, Image $image): void
    {
        static::assertSame($expected, (new Encode())->setParams($params)->run($image)->mime);
    }

    public function providerRunFromAvif(): iterable
    {
        $manager = new ImageManager();
        $jpg = $manager->canvas(100, 100)->encode('jpg');
        $png = $manager->canvas(100, 100)->encode('png');
        $gif = $manager->canvas(100, 100)->encode('gif');

        if (\function_exists('imagecreatefromavif')) {
            $avif = $manager->canvas(100, 100)->encode('avif');

            yield ['image/jpeg', ['format' => 'jpg'], $avif];
            yield ['image/jpeg', ['format' => 'pjpg'], $avif];
            yield ['image/png', ['format' => 'png'], $avif];
            yield ['image/gif', ['format' => 'gif'], $avif];
            yield ['image/avif', ['format' => 'avif'], $jpg];
            yield ['image/avif', ['format' => 'avif'], $png];
            yield ['image/avif', ['format' => 'avif'], $gif];
            yield ['image/avif', ['format' => 'avif'], $avif];
        }
    }

    /**
     * @requires function imagecreatefromavif
     * @dataProvider providerRunFromAvif
     */
    public function testRunFromAvif(string $expected, array $params, Image $image): void
    {
        static::assertSame($expected, (new Encode())->setParams($params)->run($image)->mime);
    }

    /**
     * @requires function imagecreatefromwebp
     * @requires function imagecreatefromavif
     */
    public function testRunFromWebpAvif(): void
    {
        $manager = new ImageManager();
        $webp = $manager->canvas(100, 100)->encode('webp');
        $avif = $manager->canvas(100, 100)->encode('avif');

        static::assertSame('image/webp', (new Encode())->setParams(['format' => 'webp'])->run($avif)->mime);
        static::assertSame('image/avif', (new Encode())->setParams(['format' => 'avif'])->run($webp)->mime);
    }

    public function providerFormatMatching(): iterable
    {
        yield ['jpg', ['format' => 'jpg']];
        yield ['png', ['format' => 'png']];
        yield ['gif', ['format' => 'gif']];
    }

    /** @dataProvider providerFormatMatching */
    public function testGetFormatMatching(string $expected, array $params): void
    {
        static::assertSame($expected, (new Encode())->setParams($params)->getFormat(Mockery::mock(Image::class)));
    }

    public function providerFormatUnspecifiedOrInvalid(): iterable
    {
        yield ['jpg', ['format' => null], 'image/jpeg'];
        yield ['png', ['format' => null], 'image/png'];
        yield ['gif', ['format' => null], 'image/gif'];
        yield ['jpg', ['format' => null], 'image/jpeg'];
        yield ['jpg', ['format' => ''], 'image/jpeg'];
        yield ['jpg', ['format' => 'invalid'], 'image/jpeg'];
        yield ['jpg', ['format' => 'invalid'], 'image/squirrel '];
    }

    /** @dataProvider providerFormatUnspecifiedOrInvalid */
    public function testGetFormatUnspecifiedOrInvalid(string $expected, array $params, string $mime): void
    {
        $image = Mockery::mock(Image::class, fn ($mock) => $mock->shouldReceive('mime')->andReturn($mime)->once());

        static::assertSame($expected, (new Encode())->setParams($params)->getFormat($image));
    }

    /** @requires function imagecreatefromwebp */
    public function testGetFormatWebp(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('mime')->andReturn('image/webp')->once();
        });
        static::assertSame('webp', (new Encode())->setParams(['format' => null])->getFormat($image));
    }

    /** @requires function imagecreatefromavif */
    public function testGetFormatAvif(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('mime')->andReturn('image/avif')->once();
        });
        static::assertSame('avif', (new Encode())->setParams(['format' => null])->getFormat($image));
    }

    public function providerQuality(): iterable
    {
        yield [100, ['quality' => '100']];
        yield [100, ['quality' => 100]];
        yield [90, ['quality' => null]];
        yield [90, ['quality' => 'a']];
        yield [50, ['quality' => '50.50']];
        yield [90, ['quality' => '-1']];
        yield [90, ['quality' => '101']];
    }

    /** @dataProvider providerQuality */
    public function testGetQuality(?int $expected, array $params): void
    {
        static::assertSame($expected, (new Encode())->setParams($params)->getQuality());
    }
}
