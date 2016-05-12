<?php

use Kisphp\ImageResizer;

class ThumbnailSizeCalculatorTest extends PHPUnit_Framework_TestCase
{
    const IMAGE_FILE = __DIR__ . '/../images/kisphp_image_file.jpg';

    public function setUp()
    {
        $testImage = imagecreate(200, 80);
        imagejpeg($testImage, self::IMAGE_FILE);
    }

    public function tearDown()
    {
        if ( is_file(self::IMAGE_FILE) ) {
            unlink(self::IMAGE_FILE);
        }
    }

    public function test_image_width_and_height()
    {
        $img = new ImageResizer();
        $img->load(self::IMAGE_FILE);
        $img->resize(100, 100);

        $this->assertEquals(100, $img->getFinalWidth());
        $this->assertNotEquals(100, $img->getFinalHeight());
    }

    public function test_image_width_and_height_crop()
    {
        $img = new ImageResizer();
        $img->load(self::IMAGE_FILE);
        $img->resize(100, 100, true);

        $this->assertEquals(100, $img->getFinalWidth());
        $this->assertEquals(100, $img->getFinalHeight());
    }

    public function test_image_width()
    {
        $img = new ImageResizer();
        $img->load(self::IMAGE_FILE);
        $img->resize(100, 0);

        $this->assertNotEquals(0, $img->getFinalHeight());
    }

    public function test_image_height()
    {
        $img = new ImageResizer();
        $img->load(self::IMAGE_FILE);
        $img->resize(0, 100);

        $this->assertNotEquals(0, $img->getFinalWidth());
    }
}
