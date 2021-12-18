<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators\Helpers;

use Camelot\Arbitration\Manipulators\Helpers\Dimension;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class DimensionTest extends TestCase
{
    private Image $image;

    protected function setUp(): void
    {
        $this->image = Mockery::mock('Intervention\Image\Image');
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testPixels(): void
    {
        $dimension = new Dimension($this->image);
        static::assertSame(500.0, $dimension->get('500'));
    }

    public function testRelativeWidth(): void
    {
        $this->image->shouldReceive('width')->andReturn('100')->once();

        $dimension = new Dimension($this->image);
        static::assertSame(5.0, $dimension->get('5w'));
    }

    public function testRelativeHeight(): void
    {
        $this->image->shouldReceive('height')->andReturn('100')->once();

        $dimension = new Dimension($this->image);
        static::assertSame(5.0, $dimension->get('5h'));
    }

    public function testDevicePixelRatio(): void
    {
        $dimension = new Dimension($this->image, 2);
        static::assertSame(1000.0, $dimension->get('500'));
    }

    public function testInvalidInputs(): void
    {
        $dimension = new Dimension($this->image);
        static::assertNull($dimension->get('invalid'));
        static::assertNull($dimension->get('0'));
        static::assertNull($dimension->get('-1'));
    }
}
