<?php

namespace Tests\Thumbs;

use Kisphp\ImageResizer;
use PHPUnit\Framework\TestCase;

class GeneralTest extends TestCase
{
    const IMAGES_DIR = '/../images';

    /**
     * @throws \Kisphp\ImageFileTypeNotAllowed
     */
    public function testBackground()
    {
        $source = __DIR__ . self::IMAGES_DIR . '/300x200.jpg';
        $target = __DIR__ . self::IMAGES_DIR . '/th-dummy.jpg';

        $img = new ImageResizer();
        $img->setBackgroundColor(25, 25, 25);
        $img->load($source);
        $img->setTarget($target);

        $img->resize(100, 100);

        $img->save();

        $imageContent = $img->display(true);

        $this->assertNotNull($imageContent);

        unlink($target);
    }

    /**
     * @dataProvider getDimensionsToOriginal
     *
     * @throws \Kisphp\ImageFileTypeNotAllowed
     */
    public function testOriginalDimensions($width, $height)
    {
        $source = __DIR__ . self::IMAGES_DIR . '/300x200.jpg';
        $target = __DIR__ . self::IMAGES_DIR . '/th-dummy.jpg';

        $img = new ImageResizer();
        $img->load($source);
        $img->setTarget($target);

        $img->resize($width, $height);

        $img->save();

        $size = getimagesize($target);

        $this->assertEquals(300, $size[0]);
        $this->assertEquals(200, $size[1]);

        unlink($target);
    }

    /**
     * @return array
     */
    public function getDimensionsToOriginal()
    {
        return [
            [300, 0],
            [300, 200],
            [0, 200],
            [0, 0],
        ];
    }
}
