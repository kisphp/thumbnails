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
    protected $mime;

    /**
     * @var Resource
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
     * @var int
     */
    protected $src_x = 0;

    /**
     * @var int
     */
    protected $src_y = 0;

    /**
     * @var int
     */
    protected $dst_x = 0;

    /**
     * @var int
     */
    protected $dst_y = 0;

    /**
     * @var array default white color rgb format
     */
    protected $backgroundColor = [255, 255, 255];

    /**
     * set the quality of the resulted thumbnail (for jpeg files)
     *
     * @param int $quality
     */
    public function __construct($quality = self::JPEG_QUALITY)
    {
        $this->quality = max(1, min(100, abs(intval($quality))));
    }

    /**
     * @param int $RED
     * @param int $GREEN
     * @param int $BLUE
     */
    public function setBackgroundColor($RED, $GREEN, $BLUE)
    {
        // make sure that value is between 0 and 255
        $this->backgroundColor = [
            min(255, max(0, (int)$RED)),
            min(255, max(0, (int)$GREEN)),
            min(255, max(0, (int)$BLUE)),
        ];
    }

    /**
     * load image file to resize
     *
     * @param string $sourceImageLocation = /path/to/my/file
     *
     * @throws ImageFileTypeNotAllowed
     */
    public function load($sourceImageLocation)
    {
        $this->mime = $this->getImageType($sourceImageLocation);

        switch ($this->mime) {
            case IMAGETYPE_PNG:
                $this->thumb = imagecreatefrompng($sourceImageLocation);
                // setting alpha blending off
                imagealphablending($this->thumb, false);
                // save alphablending setting (important)
                imagesavealpha($this->thumb, true);
                break;

            case IMAGETYPE_GIF:
                $this->thumb = imagecreatefromgif($sourceImageLocation);
                break;

            case IMAGETYPE_JPEG:
            case IMAGETYPE_JPEG2000:
                $this->thumb = imagecreatefromjpeg($sourceImageLocation);
                break;

            default:
                throw new ImageFileTypeNotAllowed();
        }

        $this->sourceWidth = $this->newWidth = imagesx($this->thumb);
        $this->sourceHeight = $this->newHeight = imagesy($this->thumb);
    }

    /**
     * @param string $sourceImageLocation
     *
     * @return int
     */
    protected function getImageType($sourceImageLocation)
    {
        return exif_imagetype($sourceImageLocation);
    }

    /**
     * set the width of the thumbnail and calculate the height
     *
     * @param int $width
     * @param bool $resample
     */
    protected function setWidth($width, $resample = true)
    {
        $this->newWidth = abs(intval($width));
        $this->newHeight = floor(($width / $this->sourceWidth) * $this->sourceHeight);

        if ($resample === true) {
            $this->resample();
        }
    }

    /**
     * set the height of the thumbnail and calculate the witdh
     *
     * @param int $height
     * @param bool $resample
     */
    protected function setHeight($height, $resample = true)
    {
        $this->newHeight = abs(intval($height));
        $this->newWidth = floor(($height / $this->sourceHeight) * $this->sourceWidth);

        if ($resample === true) {
            $this->resample();
        }
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
     * resample the image
     */
    protected function resample()
    {
        $thumbnailCopy = $this->thumb;
        $this->thumb = $this->newThumb();

        imagecopyresampled($this->thumb, // (dst_img) Destination image link resource.
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
            if ($this->sourceWidth >= $this->sourceHeight) {
                if (($this->sourceWidth / $this->sourceHeight) > ($width / $height)) {
                    ($cutImage === true) ? $this->setHeight($height, false) : $this->setWidth($width, false);
                } else {
                    ($cutImage === true) ? $this->setWidth($width, false) : $this->setHeight($height, false);
                }
            } else {
                if ($width >= $height) {
                    ($cutImage === true) ? $this->setWidth($width) : $this->setHeight($height, false);
                } else {
                    ($cutImage === true) ? $this->setHeight($height) : $this->setWidth($width, false);
                }
            }
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

        $this->dst_x = 0;
        $this->dst_y = 0;
        $this->src_x = 0;
        $this->src_y = 0;

        if ($this->sourceWidth >= $this->sourceHeight) {
            if ($this->sourceWidth / $this->sourceHeight > $width / $height) {
                $this->newWidth = $width;
                $this->dst_x = ($this->sourceWidth - $width) / 2;
            } else {
                $this->newHeight = $height;
                $this->dst_y = ($this->sourceHeight - $height) / 2;
            }
        } else {
            if ($width >= $height) {
                $this->newHeight = $height;
                $this->dst_y = ($this->sourceHeight - $height) / 2;
            } else {
                $this->newWidth = $width;
                $this->dst_x = ($this->sourceWidth - $width) / 2;
            }
        }

        $tmp = $this->thumb;
        $this->thumb = $this->newThumb();

        imagecopy($this->thumb, // (dst_img) Destination image link resource.
            $tmp, // (src_img) Source image link resource.
            $this->src_x, // (src_x) x-coordinate of source point.
            $this->src_y, // (src_y) y-coordinate of source point.
            $this->dst_x, // (src_x) x-coordinate of source point.
            $this->dst_y, // (src_y) y-coordinate of source point.
            $this->sourceWidth, // (sourceWidth) Source width.
            $this->sourceHeight// (sourceHeight) Source height.
        );
    }

    /**
     * resize the image and if it is below the requested dimensions puts a blank image below and merges them
     */
    protected function resampleCrop()
    {
        if ($this->originalWidth > 0 && $this->originalHeight > 0 && ($this->newWidth != $this->originalWidth || $this->newHeight != $this->originalHeight)) {
            $tmp = $this->thumb;
            $this->thumb = $this->newThumb($this->originalWidth, $this->originalHeight);

            $_top = 0;
            $_bottom = 0;

            if ($this->newWidth != $this->originalWidth) {
                $_top = ($this->originalWidth - $this->newWidth) / 2;
            }
            if ($this->newHeight != $this->originalHeight) {
                $_bottom = ($this->originalHeight - $this->newHeight) / 2;
            }
            imagecopy($this->thumb, $tmp, $_top, $_bottom, 0, 0, $this->newWidth, $this->newHeight);
        }
    }

    /**
     * generate a new thumbnail with the specified dimensions
     *
     * @param int $width width
     * @param int $height height
     *
     * @return resource|string
     */
    protected function newThumb($width = 0, $height = 0)
    {
        $_w = ($width > 0) ? $width : $this->newWidth;
        $_h = ($height > 0) ? $height : $this->newHeight;

        if ($this->mime == 'PNG') {
            $this->thumb = imagecreatetruecolor($_w, $_h);
            $color = imagecolorallocate($this->thumb, $this->backgroundColor[0], $this->backgroundColor[1],
                $this->backgroundColor[2]);
            imagefill($this->thumb, 0, 0, $color);
            // setting alpha blending off
            imagealphablending($this->thumb, false);
            // save alphablending setting (important)
            imagesavealpha($this->thumb, true);

        } else {
            $this->thumb = imagecreatetruecolor($_w, $_h);
            $color = imagecolorallocate($this->thumb, $this->backgroundColor[0], $this->backgroundColor[1],
                $this->backgroundColor[2]);
            imagefill($this->thumb, 10, 10, $color);

        }

        if (!isset($this->mime) || $this->mime == '') {
            $this->mime = 'JPG';
            $this->newWidth = $_w;
            $this->newHeight = $_h;
        }

        return $this->thumb;
    }

    /**
     * @param int $width width
     * @param int $height height
     * @param bool $cutImage used in crop files if you want to cut from it and center the thumbnail
     */
    public function resize($width = 0, $height = 0, $cutImage = false)
    {
        $this->originalWidth = $width;
        $this->originalHeight = $height;
        if ($width > 0 && $height > 0) {
            $this->crop($width, $height, $cutImage);
        } elseif ($width > 0 && $height <= 0) {
            $this->setWidth($width, true);
        } elseif ($height > 0 && $width <= 0) {
            $this->setHeight($height, true);
        }
    }

    /**
     *
     * save the file to disk
     */
    public function save()
    {
        switch ($this->mime) {
            case IMAGETYPE_PNG:
                imagepng($this->thumb, $this->target, 0);
                break;

            case IMAGETYPE_GIF:
                imagegif($this->thumb, $this->target);
                break;

            case IMAGETYPE_JPEG:
            case IMAGETYPE_JPEG2000:
                imagejpeg($this->thumb, $this->target, $this->quality);
                break;

            default:
                throw new ImageFileTypeNotAllowed();
        }
    }

    /**
     * display the image and save the file to disk (optional)
     *
     * @param bool $save
     *
     * @throws ImageFileTypeNotAllowed
     */
    public function display($save = false)
    {
        switch ($this->mime) {

            case IMAGETYPE_PNG:
                header("Content-type: image/png");
                if ($save === true) {
                    imagepng($this->thumb, $this->target, 0);
                }
                imagepng($this->thumb, null, 1);
                break;

            case IMAGETYPE_GIF:
                header("Content-type: image/gif");
                if ($save === true) {
                    imagegif($this->thumb, $this->target);
                }
                imagegif($this->thumb, null);
                break;

            case IMAGETYPE_JPEG:
            case IMAGETYPE_JPEG2000:
                header("Content-type: image/jpeg");
                if ($save === true) {
                    imagejpeg($this->thumb, $this->target, $this->quality);
                }
                imagejpeg($this->thumb, null, $this->quality);
                break;

            default:
                throw new ImageFileTypeNotAllowed();
        }
    }

    /**
     * target file where will be the image saved
     *
     * @param string $targetDirectory
     */
    public function setTarget($targetDirectory)
    {
        $this->target = $targetDirectory;
    }

    /**
     * return the image string so you will be able to save it into a file
     *
     * @return string
     */
    public function __toString()
    {
        ob_start();
        switch ($this->mime) {

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

            default:

                break;
        }

        $memoryBuffer = ob_get_clean();
        if (is_resource($this->thumb)) {
            imagedestroy($this->thumb);
        }

        return $memoryBuffer;
    }
}