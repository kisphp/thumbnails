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
//        ob_clean();
//        ob_start();
//        $img->display(true);
//        ob_clean();
//        dump(headers_list());
//        die;
        $img->save();
    }

    /**
     * @dataProvider fixedCropProvider()
     *
     * @param int $sourceWdith
     * @param int $sourceHeight
     * @param int $targetWdith
     * @param int $targetHeight
     */
    public function testCrop($sourceWdith, $sourceHeight, $targetWdith, $targetHeight)
    {
        $extensions = [
            'jpg',
            'png',
            'gif',
        ];

        foreach ($extensions as $ext) {
            $source = __DIR__ . self::IMAGES_DIR . '/' . $sourceWdith . 'x' . $sourceHeight . '.' . $ext;
            $target = __DIR__ . self::IMAGES_DIR . '/th-' . $targetWdith . 'x' . $targetHeight . '.' . $ext;

            $this->resizeImage($source, $target, $targetWdith, $targetHeight, true);

            $size = getimagesize($target);

            $this->assertEquals($targetWdith, $size[0]);
            $this->assertEquals($targetHeight, $size[1]);

            unlink($target);
        }
    }

    public function fixedCropProvider()
    {
        return [
            [300, 200, 200, 100],
            [300, 200, 200, 200],
            [300, 200, 100, 200],
            [300, 200, 10, 200],
            [300, 200, 100, 20],
            [300, 200, 50, 50],

            [200, 280, 200, 100],
            [200, 280, 200, 200],
            [200, 280, 100, 200],
            [200, 280, 10, 200],
            [200, 280, 100, 20],
            [200, 280, 50, 50],

            [200, 200, 200, 100],
            [200, 200, 200, 200],
            [200, 200, 100, 200],
            [200, 200, 10, 200],
            [200, 200, 100, 20],
            [200, 200, 50, 50],
        ];
    }

    /**
     * @dataProvider fixedResizeHeightProvider()
     *
     * @param int $sourceWdith
     * @param int $sourceHeight
     * @param int $targetWdith
     * @param int $targetHeight
     */
    public function testResizeHeight($sourceWdith, $sourceHeight, $targetWdith, $targetHeight)
    {
        $extensions = [
            'jpg',
            'png',
            'gif',
        ];

        foreach ($extensions as $ext) {
            $source = __DIR__ . self::IMAGES_DIR . '/' . $sourceWdith . 'x' . $sourceHeight . '.' . $ext;
            $target = __DIR__ . self::IMAGES_DIR . '/th-' . $targetWdith . 'x' . $targetHeight . '.' . $ext;

            $this->resizeImage($source, $target, $targetWdith, $targetHeight);

            $size = getimagesize($target);

            $this->assertEquals($targetWdith, $size[0]);

            unlink($target);
        }
    }

    public function fixedResizeHeightProvider()
    {
        return [
            [300, 200, 200, 0],
            [200, 200, 200, 0],
            [200, 280, 200, 0],
            [300, 200, 200, 10],
            [200, 200, 200, 10],
            [200, 280, 200, 10],
            [200, 200, 10, 100],
            [300, 200, 10, 100],
            [200, 280, 10, 100],
        ];
    }

    /**
     * @dataProvider fixedResizeWidthProvider()
     *
     * @param int $sourceWdith
     * @param int $sourceHeight
     * @param int $targetWdith
     * @param int $targetHeight
     */
    public function testResizeWidth($sourceWdith, $sourceHeight, $targetWdith, $targetHeight)
    {
        $extensions = [
            'jpg',
            'png',
            'gif',
        ];

        foreach ($extensions as $ext) {
            $source = __DIR__ . self::IMAGES_DIR . '/' . $sourceWdith . 'x' . $sourceHeight . '.' . $ext;
            $target = __DIR__ . self::IMAGES_DIR . '/th-' . $targetWdith . 'x' . $targetHeight . '.' . $ext;

            $this->resizeImage($source, $target, $targetWdith, $targetHeight, true);

            $size = getimagesize($target);

            $this->assertEquals($targetHeight, $size[1]);

            unlink($target);
        }
    }

    public function fixedResizeWidthProvider()
    {
        return [
            [300, 200, 10, 100],
            [200, 200, 10, 100],
            [200, 280, 10, 100],
        ];
    }
}