<?php

namespace Thumbs;

use Kisphp\ImageFile;
use Kisphp\ResizeManager;

class ResizeManagerTest extends \PHPUnit_Framework_TestCase
{
    const IMAGE_FILE = '/tmp/kisphp_image_file.jpg';

    public function createFile($width, $height)
    {
        $image = imagecreate($width, $height);
        imagejpeg($image, self::IMAGE_FILE);
    }

    public function testResize()
    {
        $this->createFile(1920, 2500);
        $a = new ResizeManager();

        $img = new ImageFile(self::IMAGE_FILE);

        $b = $a->getResizeDimensions($img, 270, 300);

        var_dump($b);
    }
}
