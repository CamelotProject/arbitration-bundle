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
    private Encode $manipulator;
    private Image $jpg;
    private Image $png;
    private Image $gif;
    private Image $webp;
    private Image $avif;

    protected function setUp(): void
    {
        $manager = new ImageManager();
        $this->jpg = $manager->canvas(100, 100)->encode('jpg');
        $this->png = $manager->canvas(100, 100)->encode('png');
        $this->gif = $manager->canvas(100, 100)->encode('gif');

        if (\function_exists('imagecreatefromwebp')) {
            $this->webp = $manager->canvas(100, 100)->encode('webp');
        }

        if (\function_exists('imagecreatefromavif')) {
            $this->avif = $manager->canvas(100, 100)->encode('avif');
        }

        $this->manipulator = new Encode();
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateInstance(): void
    {
        static::assertInstanceOf(Encode::class, $this->manipulator);
    }

    public function testRun(): void
    {
        static::assertSame('image/jpeg', $this->manipulator->setParams(['format' => 'jpg'])->run($this->jpg)->mime);
        static::assertSame('image/jpeg', $this->manipulator->setParams(['format' => 'jpg'])->run($this->png)->mime);
        static::assertSame('image/jpeg', $this->manipulator->setParams(['format' => 'jpg'])->run($this->gif)->mime);
        static::assertSame('image/jpeg', $this->manipulator->setParams(['format' => 'pjpg'])->run($this->jpg)->mime);
        static::assertSame('image/jpeg', $this->manipulator->setParams(['format' => 'pjpg'])->run($this->png)->mime);
        static::assertSame('image/jpeg', $this->manipulator->setParams(['format' => 'pjpg'])->run($this->gif)->mime);
        static::assertSame('image/png', $this->manipulator->setParams(['format' => 'png'])->run($this->jpg)->mime);
        static::assertSame('image/png', $this->manipulator->setParams(['format' => 'png'])->run($this->png)->mime);
        static::assertSame('image/png', $this->manipulator->setParams(['format' => 'png'])->run($this->gif)->mime);
        static::assertSame('image/gif', $this->manipulator->setParams(['format' => 'gif'])->run($this->jpg)->mime);
        static::assertSame('image/gif', $this->manipulator->setParams(['format' => 'gif'])->run($this->png)->mime);
        static::assertSame('image/gif', $this->manipulator->setParams(['format' => 'gif'])->run($this->gif)->mime);

        if (\function_exists('imagecreatefromwebp')) {
            static::assertSame('image/jpeg', $this->manipulator->setParams(['format' => 'jpg'])->run($this->webp)->mime);
            static::assertSame('image/jpeg', $this->manipulator->setParams(['format' => 'pjpg'])->run($this->webp)->mime);
            static::assertSame('image/png', $this->manipulator->setParams(['format' => 'png'])->run($this->webp)->mime);
            static::assertSame('image/gif', $this->manipulator->setParams(['format' => 'gif'])->run($this->webp)->mime);
            static::assertSame('image/webp', $this->manipulator->setParams(['format' => 'webp'])->run($this->jpg)->mime);
            static::assertSame('image/webp', $this->manipulator->setParams(['format' => 'webp'])->run($this->png)->mime);
            static::assertSame('image/webp', $this->manipulator->setParams(['format' => 'webp'])->run($this->gif)->mime);
            static::assertSame('image/webp', $this->manipulator->setParams(['format' => 'webp'])->run($this->webp)->mime);
        }
        if (\function_exists('imagecreatefromavif')) {
            static::assertSame('image/jpeg', $this->manipulator->setParams(['format' => 'jpg'])->run($this->avif)->mime);
            static::assertSame('image/jpeg', $this->manipulator->setParams(['format' => 'pjpg'])->run($this->avif)->mime);
            static::assertSame('image/png', $this->manipulator->setParams(['format' => 'png'])->run($this->avif)->mime);
            static::assertSame('image/gif', $this->manipulator->setParams(['format' => 'gif'])->run($this->avif)->mime);
            static::assertSame('image/avif', $this->manipulator->setParams(['format' => 'avif'])->run($this->jpg)->mime);
            static::assertSame('image/avif', $this->manipulator->setParams(['format' => 'avif'])->run($this->png)->mime);
            static::assertSame('image/avif', $this->manipulator->setParams(['format' => 'avif'])->run($this->gif)->mime);
            static::assertSame('image/avif', $this->manipulator->setParams(['format' => 'avif'])->run($this->avif)->mime);
        }

        if (\function_exists('imagecreatefromwebp') && \function_exists('imagecreatefromavif')) {
            static::assertSame('image/webp', $this->manipulator->setParams(['format' => 'webp'])->run($this->avif)->mime);
            static::assertSame('image/avif', $this->manipulator->setParams(['format' => 'avif'])->run($this->webp)->mime);
        }
    }

    public function testGetFormat(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('mime')->andReturn('image/jpeg')->once();
            $mock->shouldReceive('mime')->andReturn('image/png')->once();
            $mock->shouldReceive('mime')->andReturn('image/gif')->once();
            $mock->shouldReceive('mime')->andReturn('image/bmp')->once();
            $mock->shouldReceive('mime')->andReturn('image/jpeg')->twice();

            if (\function_exists('imagecreatefromwebp')) {
                $mock->shouldReceive('mime')->andReturn('image/webp')->once();
            }
            if (\function_exists('imagecreatefromavif')) {
                $mock->shouldReceive('mime')->andReturn('image/avif')->once();
            }
        });

        static::assertSame('jpg', $this->manipulator->setParams(['format' => 'jpg'])->getFormat($image));
        static::assertSame('png', $this->manipulator->setParams(['format' => 'png'])->getFormat($image));
        static::assertSame('gif', $this->manipulator->setParams(['format' => 'gif'])->getFormat($image));
        static::assertSame('jpg', $this->manipulator->setParams(['format' => null])->getFormat($image));
        static::assertSame('png', $this->manipulator->setParams(['format' => null])->getFormat($image));
        static::assertSame('gif', $this->manipulator->setParams(['format' => null])->getFormat($image));
        static::assertSame('jpg', $this->manipulator->setParams(['format' => null])->getFormat($image));
        static::assertSame('jpg', $this->manipulator->setParams(['format' => ''])->getFormat($image));
        static::assertSame('jpg', $this->manipulator->setParams(['format' => 'invalid'])->getFormat($image));

        if (\function_exists('imagecreatefromwebp')) {
            static::assertSame('webp', $this->manipulator->setParams(['format' => null])->getFormat($image));
        }

        if (\function_exists('imagecreatefromavif')) {
            static::assertSame('avif', $this->manipulator->setParams(['format' => null])->getFormat($image));
        }
    }

    public function testGetQuality(): void
    {
        static::assertSame(100, $this->manipulator->setParams(['quality' => '100'])->getQuality());
        static::assertSame(100, $this->manipulator->setParams(['quality' => 100])->getQuality());
        static::assertSame(90, $this->manipulator->setParams(['quality' => null])->getQuality());
        static::assertSame(90, $this->manipulator->setParams(['quality' => 'a'])->getQuality());
        static::assertSame(50, $this->manipulator->setParams(['quality' => '50.50'])->getQuality());
        static::assertSame(90, $this->manipulator->setParams(['quality' => '-1'])->getQuality());
        static::assertSame(90, $this->manipulator->setParams(['quality' => '101'])->getQuality());
    }
}
