<?php

namespace Kisphp;

class ImageResizer
{
    const JPEG_QUALITY = 85;

    /**
     * @var int
     */
    protected $quality = self::JPEG_QUALITY;

    /**
     * @var int
     */
    protected $mimeType;

    /**
     * @var resource
     */
    protected $thumb;

    /**
     * @var string
     */
    protected $target;

    /**
     * @var int
     */
    protected $sourceWidth = 0;

    /**
     * @var int
     */
    protected $sourceHeight = 0;

    /**
     * @var int
     */
    protected $originalWidth = 0;

    /**
     * @var int
     */
    protected $originalHeight = 0;

    /**
     * @var int
     */
    protected $newWidth = 0;

    /**
     * @var int
     */
    protected $newHeight = 0;

    /**
     * @var float|int
     */
    protected $sourceX = 0;

    /**
     * @var float|int
     */
    protected $sourceY = 0;

    /**
     * @var float|int
     */
    protected $destinationX = 0;

    /**
     * @var float|int
     */
    protected $destinationY = 0;

    /**
     * @var array default white color rgb format
     */
    protected $backgroundColor = [255, 255, 255];

    /**
     * @var string
     */
    protected $contentType;

    /**
     * @var string
     */
    protected $imageAsString;

    /**
     * set the quality of the resulted thumbnail (for jpeg files).
     *
     * @param int $quality
     */
    public function __construct($quality = self::JPEG_QUALITY)
    {
        $this->quality = max(1, min(100, abs((int) $quality)));
    }

    /**
     * @return int new width for image
     */
    public function getFinalWidth()
    {
        return $this->newWidth;
    }

    /**
     * @return int new height for image
     */
    public function getFinalHeight()
    {
        return $this->newHeight;
    }

    /**
     * @param int $red
     * @param int $green
     * @param int $blue
     */
    public function setBackgroundColor($red, $green, $blue)
    {
        // make sure that each value is between 0 and 255
        $this->backgroundColor = [
            min(255, max(0, (int) $red)),
            min(255, max(0, (int) $green)),
            min(255, max(0, (int) $blue)),
        ];
    }

    /**
     * load image file to resize.
     *
     * @param string $sourceImageLocation = /path/to/my/file
     *
     * @throws ImageFileTypeNotAllowed
     */
    public function load($sourceImageLocation)
    {
        $this->mimeType = $this->getImageType($sourceImageLocation);

        switch ($this->mimeType) {
            case IMAGETYPE_PNG:
                $this->contentType = 'image/png';
                $this->thumb = imagecreatefrompng($sourceImageLocation);
                // setting alpha blending off
                imagealphablending($this->thumb, false);
                // save alphablending setting (important)
                imagesavealpha($this->thumb, true);

                break;
            case IMAGETYPE_GIF:
                $this->contentType = 'image/gif';
                $this->thumb = imagecreatefromgif($sourceImageLocation);

                break;
            case IMAGETYPE_JPEG:
            case IMAGETYPE_JPEG2000:
                $this->contentType = 'image/jpeg';
                $this->thumb = imagecreatefromjpeg($sourceImageLocation);

                break;
            default:
                throw new ImageFileTypeNotAllowed('This is not a valid image type');
        }

        $this->sourceWidth = $this->newWidth = imagesx($this->thumb);
        $this->sourceHeight = $this->newHeight = imagesy($this->thumb);
    }

    /**
     * @param int $width
     * @param int $height
     * @param bool $cutImage used in crop files if you want to cut from it and center the thumbnail
     */
    public function resize($width = 0, $height = 0, $cutImage = false)
    {
        $this->originalWidth = $width;
        $this->originalHeight = $height;

        if ($width > 0 && $height > 0) {
            $this->crop($width, $height, $cutImage);

            return;
        }

        if ($width > 0 && $height <= 0) {
            $this->setWidth($width, true);

            return;
        }

        if ($height > 0 && $width <= 0) {
            $this->setHeight($height, true);
        }
    }

    /**
     * @return bool
     */
    public function save()
    {
        $imageString = $this->getImageString();

        return (bool) file_put_contents($this->target, $imageString);
    }

    /**
     * display the image and save the file to disk (optional).
     *
     * @param bool|false $save
     *
     * @throws ImageFileTypeNotAllowed
     *
     * @return string
     */
    public function display($save = false)
    {
        $imageString = $this->getImageString();

        if ($save === true) {
            $this->save();
        }
        if (!headers_sent()) {
            header('Content-Type: ' . $this->mimeType);
        }

        return $imageString;
    }

    /**
     * @return string
     */
    public function getImageString()
    {
        if ($this->imageAsString !== null) {
            return $this->imageAsString;
        }

        ob_start();
        switch ($this->mimeType) {
            case IMAGETYPE_PNG:
                imagepng($this->thumb, null, 1);

                break;
            case IMAGETYPE_GIF:
                imagegif($this->thumb, null);

                break;
            case IMAGETYPE_JPEG:
            case IMAGETYPE_JPEG2000:
                imagejpeg($this->thumb, null, $this->quality);

                break;
        }
        $this->imageAsString = ob_get_clean();

        return $this->imageAsString;
    }

    /**
     * target file where will be the image saved.
     *
     * @param string $targetDirectory
     */
    public function setTarget($targetDirectory)
    {
        $this->target = $targetDirectory;
    }

    /**
     * @param string $sourceImageLocation
     *
     * @return int
     */
    protected function getImageType($sourceImageLocation)
    {
        if (is_file($sourceImageLocation) === false) {
            return IMAGETYPE_JPEG;
        }

        return \exif_imagetype($sourceImageLocation);
    }

    /**
     * set the width of the thumbnail and calculate the height.
     *
     * @param int $width
     * @param bool $resample
     */
    protected function setWidth($width, $resample = true)
    {
        $this->newWidth = abs((int) $width);
        $this->newHeight = floor(($width / $this->sourceWidth) * $this->sourceHeight);

        if ($resample === true) {
            $this->resample();
        }
    }

    /**
     * set the height of the thumbnail and calculate the witdh.
     *
     * @param int $height
     * @param bool $resample
     */
    protected function setHeight($height, $resample = true)
    {
        $this->newHeight = abs((int) $height);
        $this->newWidth = floor(($height / $this->sourceHeight) * $this->sourceWidth);

        if ($resample === true) {
            $this->resample();
        }
    }

    /**
     * resample the image.
     *
     * @throws \Exception
     */
    protected function resample()
    {
        $thumbnailCopy = $this->thumb;
        $this->thumb = $this->newThumb();

        imagecopyresampled(
            $this->thumb, // (dst_img) Destination image link resource.
            $thumbnailCopy, // (src_img) Source image link resource.
            0, // (dst_x) x-coordinate of destination point.
            0, // (dst_y) y-coordinate of destination point.
            0, // (src_x) x-coordinate of source point.
            0, // (src_y) y-coordinate of source point.
            $this->newWidth, // (dst_w) Destination width.
            $this->newHeight, // (dst_h) Destination height.
            $this->sourceWidth, // (sourceWidth) Source width.
            $this->sourceHeight // (sourceHeight) Source height.
        );
        unset($thumbnailCopy);
    }

    /**
     * @param int $width width
     * @param int $height height
     * @param bool $cutImage
     */
    protected function crop($width, $height, $cutImage = false)
    {
        if ($cutImage === true) {
            $this->doSimpleCrop($width, $height);
        } else {
            $this->setNewSize($width, $height, $cutImage);
            $this->resample();
            $this->resampleCrop();
        }
    }

    /**
     * @param int $width width
     * @param int $height height
     */
    protected function doSimpleCrop($width, $height)
    {
        if ($this->sourceWidth >= $this->sourceHeight) {
            if (($this->sourceWidth / $this->sourceHeight) > ($width / $height)) {
                $this->setHeight($height, false);
            } else {
                $this->setWidth($width, false);
            }
        } else {
            if ($width >= $height) {
                $this->setWidth($width, false);
            } else {
                $this->setHeight($height, false);
            }
        }
        $this->resample();

        $this->sourceWidth = $this->newWidth;
        $this->sourceHeight = $this->newHeight;

        $this->destinationX = 0;
        $this->destinationY = 0;
        $this->sourceX = 0;
        $this->sourceY = 0;

        if ($this->sourceWidth >= $this->sourceHeight) {
            if ($this->sourceWidth / $this->sourceHeight > $width / $height) {
                $this->newWidth = $width;
                $this->destinationX = ($this->sourceWidth - $width) / 2;
            } else {
                $this->newHeight = $height;
                $this->destinationY = ($this->sourceHeight - $height) / 2;
            }
        } else {
            if ($width >= $height) {
                $this->newHeight = $height;
                $this->destinationY = ($this->sourceHeight - $height) / 2;
            } else {
                $this->newWidth = $width;
                $this->destinationX = ($this->sourceWidth - $width) / 2;
            }
        }

        $tmp = $this->thumb;
        $this->thumb = $this->newThumb();

        imagecopy(
            $this->thumb, // (dst_img) Destination image link resource.
            $tmp, // (src_img) Source image link resource.
            $this->sourceX, // (src_x) x-coordinate of source point.
            $this->sourceY, // (src_y) y-coordinate of source point.
            $this->destinationX, // (src_x) x-coordinate of source point.
            $this->destinationY, // (src_y) y-coordinate of source point.
            $this->sourceWidth, // (sourceWidth) Source width.
            $this->sourceHeight// (sourceHeight) Source height.
        );
    }

    /**
     * resize the image and if it is below the requested dimensions puts a blank image below and merges them.
     */
    protected function resampleCrop()
    {
        if ($this->originalWidth > 0 && $this->originalHeight > 0 && ($this->newWidth !== $this->originalWidth || $this->newHeight !== $this->originalHeight)) {
            $tmp = $this->thumb;
            $this->thumb = $this->newThumb($this->originalWidth, $this->originalHeight);

            $top = 0;
            $bottom = 0;

            if ($this->newWidth !== $this->originalWidth) {
                $top = ($this->originalWidth - $this->newWidth) / 2;
            }
            if ($this->newHeight !== $this->originalHeight) {
                $bottom = ($this->originalHeight - $this->newHeight) / 2;
            }
            imagecopy($this->thumb, $tmp, $top, $bottom, 0, 0, $this->newWidth, $this->newHeight);
        }
    }

    /**
     * generate a new thumbnail with the specified dimensions.
     *
     * @param int $width width
     * @param int $height height
     *
     * @throws \Exception
     *
     * @return resource|string
     */
    protected function newThumb($width = 0, $height = 0)
    {
        $calculatedWidth = ($width > 0) ? $width : $this->newWidth;
        $calculatedHeight = ($height > 0) ? $height : $this->newHeight;

        if ($this->mimeType === IMAGETYPE_PNG) {
            $this->thumb = imagecreatetruecolor($calculatedWidth, $calculatedHeight);
            $color = imagecolorallocate(
                $this->thumb,
                $this->backgroundColor[0],
                $this->backgroundColor[1],
                $this->backgroundColor[2]
            );
            imagefill($this->thumb, 0, 0, $color);
            // setting alpha blending off
            imagealphablending($this->thumb, false);
            // save alphablending setting (important)
            imagesavealpha($this->thumb, true);

            return $this->thumb;
        }

        $this->thumb = imagecreatetruecolor($calculatedWidth, $calculatedHeight);
        $color = imagecolorallocate(
            $this->thumb,
            $this->backgroundColor[0],
            $this->backgroundColor[1],
            $this->backgroundColor[2]
        );
        imagefill($this->thumb, 10, 10, $color);

        return $this->thumb;
    }

    /**
     * @param int $width
     * @param int $height
     * @param bool $cutImage
     */
    protected function setNewSize($width, $height, $cutImage)
    {
        // landscape
        if ($this->sourceWidth >= $this->sourceHeight) {
            // keep landscape
            if (($this->sourceWidth / $this->sourceHeight) > ($width / $height)) {
                ($cutImage === true)
                    // cut image by height
                    ? $this->setHeight($height, false)
                    : $this->setWidth($width, false);
            } else {
                ($cutImage === true)
                    ? $this->setWidth($width, false)
                    : $this->setHeight($height, false);
            }

            return;
        }

        // original is portrait

        // portrait to landscape
        if ($width >= $height) {
            ($cutImage === true)
                // cut image by withh
                ? $this->setWidth($width)
                // cut image by height
                : $this->setHeight($height, false);
        } else {
            ($cutImage === true)
                ? $this->setHeight($height)
                : $this->setWidth($width, false);
        }
    }
}
