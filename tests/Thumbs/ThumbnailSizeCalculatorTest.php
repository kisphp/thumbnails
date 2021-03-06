<?php

namespace Tests\Thumbs;

use Kisphp\ImageResizer;
use PHPUnit\Framework\TestCase;

class ThumbnailSizeCalculatorTest extends TestCase
{
    const IMAGE_FILE = '/../images/kisphp_image_file.jpg';

    public function setUp() : void
    {
        $testImage = imagecreate(200, 80);
        imagejpeg($testImage, __DIR__ . self::IMAGE_FILE);
    }

    public function tearDown() : void
    {
        if ( is_file(__DIR__ . self::IMAGE_FILE) ) {
            unlink(__DIR__ . self::IMAGE_FILE);
        }
    }

    public function test_image_width_and_height()
    {
        $img = new ImageResizer();
        $img->load(__DIR__ . self::IMAGE_FILE);
        $img->resize(100, 100);

        $this->assertEquals(100, $img->getFinalWidth());
        $this->assertNotEquals(100, $img->getFinalHeight());
    }

    public function test_image_width_and_height_crop()
    {
        $img = new ImageResizer();
        $img->load(__DIR__ . self::IMAGE_FILE);
        $img->resize(100, 100, true);

        $this->assertEquals(100, $img->getFinalWidth());
        $this->assertEquals(100, $img->getFinalHeight());
    }

    public function test_image_width()
    {
        $img = new ImageResizer();
        $img->load(__DIR__ . self::IMAGE_FILE);
        $img->resize(100, 0);

        $this->assertNotEquals(0, $img->getFinalHeight());
    }

    public function test_image_height()
    {
        $img = new ImageResizer();
        $img->load(__DIR__ . self::IMAGE_FILE);
        $img->resize(0, 100);

        $this->assertNotEquals(0, $img->getFinalWidth());
    }
}
