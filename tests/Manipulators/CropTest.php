<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Manipulators;

use Camelot\Arbitration\Manipulators\Crop;
use Intervention\Image\Image;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Camelot\Arbitration\Manipulators\BaseManipulator
 * @covers \Camelot\Arbitration\Manipulators\Crop
 *
 * @internal
 */
final class CropTest extends TestCase
{
    private Image $image;

    protected function setUp(): void
    {
        $this->image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('width')->andReturn(100);
            $mock->shouldReceive('height')->andReturn(100);
        });
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateInstance(): void
    {
        static::assertInstanceOf(Crop::class, new Crop());
    }

    public function testRun(): void
    {
        $this->image->shouldReceive('crop')->with(100, 100, 0, 0)->once();

        static::assertInstanceOf(Image::class, (new Crop())->setParams(['crop' => '100,100,0,0'])->run($this->image));
    }

    public function providerCoordinates(): iterable
    {
        yield [[100, 100, 0, 0], ['crop' => '100,100,0,0']];
        yield [[101, 1, 1, 1], ['crop' => '101,1,1,1']];
        yield [[1, 101, 1, 1], ['crop' => '1,101,1,1']];
        yield [null, ['crop' => null]];
        yield [null, ['crop' => '1,1,1,']];
        yield [null, ['crop' => '1,1,,1']];
        yield [null, ['crop' => '1,,1,1']];
        yield [null, ['crop' => ',1,1,1']];
        yield [null, ['crop' => '-1,1,1,1']];
        yield [null, ['crop' => '1,1,101,1']];
        yield [null, ['crop' => '1,1,1,101']];
        yield [null, ['crop' => 'a']];
        yield [null, ['crop' => '']];
    }

    /** @dataProvider providerCoordinates */
    public function testGetCoordinates(?array $expected, array $params): void
    {
        static::assertSame($expected, (new Crop())->setParams($params)->getCoordinates($this->image));
    }

    public function providerValidateCoordinates(): iterable
    {
        yield [[100, 100, 0, 0], [100, 100, 0, 0]];
        yield [[90, 90, 10, 10], [100, 100, 10, 10]];
    }

    /** @dataProvider providerValidateCoordinates */
    public function testValidateCoordinates(?array $expected, array $params): void
    {
        static::assertSame($expected, (new Crop())->limitToImageBoundaries($this->image, $params));
    }
}
