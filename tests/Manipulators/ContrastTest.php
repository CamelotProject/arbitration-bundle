<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Manipulators\Contrast;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ContrastTest extends TestCase
{
    private Contrast $manipulator;

    protected function setUp(): void
    {
        $this->manipulator = new Contrast();
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateInstance(): void
    {
        static::assertInstanceOf('League\Glide\Manipulators\Contrast', $this->manipulator);
    }

    public function testRun(): void
    {
        $image = Mockery::mock('Intervention\Image\Image', function ($mock): void {
            $mock->shouldReceive('contrast')->with('50')->once();
        });

        static::assertInstanceOf(
            'Intervention\Image\Image',
            $this->manipulator->setParams(['con' => 50])->run($image)
        );
    }

    public function testGetPixelate(): void
    {
        static::assertSame(50, $this->manipulator->setParams(['con' => '50'])->getContrast());
        static::assertSame(50, $this->manipulator->setParams(['con' => 50])->getContrast());
        static::assertNull($this->manipulator->setParams(['con' => null])->getContrast());
        static::assertNull($this->manipulator->setParams(['con' => '101'])->getContrast());
        static::assertNull($this->manipulator->setParams(['con' => '-101'])->getContrast());
        static::assertNull($this->manipulator->setParams(['con' => 'a'])->getContrast());
    }
}
