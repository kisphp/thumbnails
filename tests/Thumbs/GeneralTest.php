<?php


class GeneralTest extends PHPUnit_Framework_TestCase
{
    const IMAGES_DIR = '/../images';

    /**
     * @throws \Kisphp\ImageFileTypeNotAllowed
     */
    public function testBackground()
    {
        $source = __DIR__ . self::IMAGES_DIR . '/300x200.jpg';
        $target = __DIR__ . self::IMAGES_DIR . '/th-dummy.jpg';

        $im = new \Kisphp\ImageResizer();
        $im->setBackgroundColor(25, 25, 25);
        $im->load($source);
        $im->setTarget($target);

        $im->resize(100, 100);

        $im->save();

        $imageContent = $im->display(true);

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

        $im = new \Kisphp\ImageResizer();
        $im->load($source);
        $im->setTarget($target);

        $im->resize($width, $height);

        $im->save();

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