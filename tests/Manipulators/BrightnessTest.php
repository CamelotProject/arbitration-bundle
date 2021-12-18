<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Manipulators\Brightness;
use Intervention\Image\Image;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class BrightnessTest extends TestCase
{
    private Brightness $manipulator;

    protected function setUp(): void
    {
        $this->manipulator = new Brightness();
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateInstance(): void
    {
        static::assertInstanceOf(Brightness::class, $this->manipulator);
    }

    public function testRun(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('brightness')->with('50')->once();
        });

        static::assertInstanceOf(Image::class, $this->manipulator->setParams(['bri' => 50])->run($image));
    }

    public function testGetPixelate(): void
    {
        static::assertSame(50, $this->manipulator->setParams(['bri' => '50'])->getBrightness());
        static::assertSame(50, $this->manipulator->setParams(['bri' => 50])->getBrightness());
        static::assertNull($this->manipulator->setParams(['bri' => null])->getBrightness());
        static::assertNull($this->manipulator->setParams(['bri' => '101'])->getBrightness());
        static::assertNull($this->manipulator->setParams(['bri' => '-101'])->getBrightness());
        static::assertNull($this->manipulator->setParams(['bri' => 'a'])->getBrightness());
    }
}
