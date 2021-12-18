<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Manipulators\Flip;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class FlipTest extends TestCase
{
    private Flip $manipulator;

    protected function setUp(): void
    {
        $this->manipulator = new Flip();
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateInstance(): void
    {
        static::assertInstanceOf('League\Glide\Manipulators\Flip', $this->manipulator);
    }

    public function testRun(): void
    {
        $image = Mockery::mock('Intervention\Image\Image', function ($mock): void {
            $mock->shouldReceive('flip')->andReturn($mock)->with('h')->once();
            $mock->shouldReceive('flip')->andReturn($mock)->with('v')->once();
        });

        static::assertInstanceOf(
            'Intervention\Image\Image',
            $this->manipulator->setParams(['flip' => 'h'])->run($image)
        );

        static::assertInstanceOf(
            'Intervention\Image\Image',
            $this->manipulator->setParams(['flip' => 'v'])->run($image)
        );
    }

    public function testGetFlip(): void
    {
        static::assertSame('h', $this->manipulator->setParams(['flip' => 'h'])->getFlip());
        static::assertSame('v', $this->manipulator->setParams(['flip' => 'v'])->getFlip());
    }
}
