<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Manipulators\Brightness;
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
        static::assertInstanceOf('League\Glide\Manipulators\Brightness', $this->manipulator);
    }

    public function testRun(): void
    {
        $image = Mockery::mock('Intervention\Image\Image', function ($mock): void {
            $mock->shouldReceive('brightness')->with('50')->once();
        });

        static::assertInstanceOf(
            'Intervention\Image\Image',
            $this->manipulator->setParams(['bri' => 50])->run($image)
        );
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
