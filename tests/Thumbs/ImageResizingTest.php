<?php


class ImageResizingTest extends PHPUnit_Framework_TestCase
{
    const IMAGES_DIR = '/../images';

    /**
     * @param string $source
     * @param string $target
     * @param int $width
     * @param int $height
     * @throws \Kisphp\ImageFileTypeNotAllowed
     */
    protected function resizeImage($source, $target, $width, $height, $cutImage = false)
    {
        $img = new \Kisphp\ImageResizer();
        $img->load($source);
        $img->setTarget($target);
        $img->resize($width, $height, $cutImage);
        $img->save();
    }

    /**
     * @dataProvider fixedResizeProvider()
     *
     * @param int $sourceWdith
     * @param int $sourceHeight
     * @param int $targetWdith
     * @param int $targetHeight
     */
    public function testResizing($sourceWdith, $sourceHeight, $targetWdith, $targetHeight)
    {
        $source = __DIR__ . self::IMAGES_DIR . '/' . $sourceWdith . 'x' . $sourceHeight . '.jpg';
        $target = __DIR__ . self::IMAGES_DIR . '/th-' . $targetWdith . 'x' . $targetHeight . '.jpg';

        $this->resizeImage($source, $target, $targetWdith, $targetHeight);

        $size = getimagesize($target);

        $this->assertEquals($targetWdith, $size[0]);
        $this->assertEquals($targetHeight, $size[1]);

        unlink($target);
    }

    public function fixedResizeProvider()
    {
        return [
            [300, 200, 200, 100],
            [300, 200, 200, 200],
            [300, 200, 100, 200],

            [200, 280, 200, 100],
            [200, 280, 200, 200],
            [200, 280, 100, 200],
        ];
    }
}