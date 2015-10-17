<?php

namespace Kisphp;

use Kisphp\Types\ImageFileJpeg;
use Kisphp\Types\ImageFileTrueColor;

abstract class ImageFile implements ImageFileInterface
{
    protected $imageResource;

    protected $width;
    protected $height;

    protected $history = [];

    const BACKGROUND_VALUE_RED = 255;
    const BACKGROUND_VALUE_GREEN = 255;
    const BACKGROUND_VALUE_BLUE = 255;

    /**
     * @param $sourceWidth
     * @param $sourceHeight
     */
    protected function __construct($imageResource, $sourceWidth, $sourceHeight)
    {
        $this->setImageResource($imageResource);
        $this->width = $sourceWidth;
        $this->height = $sourceHeight;
        $this->addHistory($sourceWidth, $sourceHeight);
    }

    /**
     * @param int $width
     * @param int $height
     *
     * @return ImageFileTrueColor
     */
    public static function generateBlankImage($width, $height)
    {
        $image = imagecreatetruecolor($width, $height);
        imagecolorallocate(
            $image,
            ImageFile::BACKGROUND_VALUE_RED,
            ImageFile::BACKGROUND_VALUE_BLUE,
            ImageFile::BACKGROUND_VALUE_GREEN
        );

        $imageObject = new ImageFileTrueColor($image, $width, $height);

        return $imageObject;
    }

    public static function createFromFile($pathToImageFile)
    {
        $imageResource = imagecreatefromjpeg($pathToImageFile);
        $imageSize = getimagesize($pathToImageFile);
        list($width, $height, , $attr) = $imageSize;

        return new ImageFileJpeg($imageResource, $width, $height);
    }

    /**
     * @return mixed
     */
    public function getSourceWidth()
    {
        return $this->width;
    }

    /**
     * @param mixed $sourceWidth
     */
    public function setSourceWidth($sourceWidth)
    {
        $this->width = $sourceWidth;
    }

    /**
     * @return mixed
     */
    public function getSourceHeight()
    {
        return $this->height;
    }

    /**
     * @param mixed $sourceHeight
     */
    public function setSourceHeight($sourceHeight)
    {
        $this->height = $sourceHeight;
    }

    /**
     * @return array
     */
    public function getHistory()
    {
        return $this->history;
    }

    /**
     * @param int $width
     * @param int $height
     */
    public function addHistory($width, $height)
    {
        $this->history[] = (int) $width . 'x' . (int) $height;
    }

    /**
     * @return mixed
     */
    public function getImageResource()
    {
        return $this->imageResource;
    }

    /**
     * @param mixed $imageResource
     */
    public function setImageResource($imageResource)
    {
        $this->imageResource = $imageResource;
    }
}
