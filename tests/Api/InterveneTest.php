<?php

declare(strict_types=1);

namespace Camelot\Arbitration\Tests\Api;

use Camelot\Arbitration\Api\Intervene;
use Camelot\Arbitration\Manipulators\ManipulatorInterface;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 * @covers \Camelot\Arbitration\Api\Intervene
 */
final class InterveneTest extends TestCase
{
    private Intervene $api;

    protected function setUp(): void
    {
        $this->api = new Intervene(Mockery::mock(ImageManager::class), []);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreateInstance(): void
    {
        static::assertInstanceOf(Intervene::class, $this->api);
    }

    public function testRun(): void
    {
        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('getEncoded')->andReturn('encoded');
        });

        $manager = Mockery::mock(ImageManager::class, function ($mock) use ($image): void {
            $mock->shouldReceive('make')->andReturn($image);
        });

        $manipulator = Mockery::mock(ManipulatorInterface::class, function ($mock) use ($image): void {
            $mock->shouldReceive('setParams')->with([]);
            $mock->shouldReceive('run')->andReturn($image);
        });

        $api = new Intervene($manager, [$manipulator]);

        static::assertSame('encoded', $api->handle('source', []));
    }

    public function testRunWithInvalidManipulator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Manipulator stdClass does not implement ' . ManipulatorInterface::class);

        $image = Mockery::mock(Image::class, function ($mock): void {
            $mock->shouldReceive('getEncoded')->andReturn('encoded');
        });

        $manager = Mockery::mock(ImageManager::class, function ($mock) use ($image): void {
            $mock->shouldReceive('make')->andReturn($image);
        });

        $api = new Intervene($manager, [new stdClass()]);

        $api->handle('source', []);
    }
}
