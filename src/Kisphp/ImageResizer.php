<?php

namespace Kisphp;

class ImageResizer
{
    protected $quality = 85;
    protected $mime    = '';
    protected $thumb   = '';
    protected $target  = '';

    protected $sourceWidth  = 0;
    protected $sourceHeight = 0;

    private $originalWidth  = 0;
    private $originalHeight = 0;
    private $newWidth       = 0;
    private $newHeight      = 0;

    /**
     * @var array default white color rgb format
     */
    protected $backgroundColor = array(255, 255, 255);

    /**
     * set the quality of the resulted thumbnail (for jpeg files)
     *
     * @param int $quality
     */
    public function __construct($quality = 85)
    {
        $this->quality = max(1, min(100, abs(intval($quality))));
    }

    /**
     * @param integer $RED
     * @param integer $GREEN
     * @param integer $BLUE
     */
    public function setBackgroundColor($RED, $GREEN, $BLUE)
    {
        // make sure that value is between 0 and 255
        $this->backgroundColor = array(
            min(255, max(0, (int) $RED)),
            min(255, max(0, (int) $GREEN)),
            min(255, max(0, (int) $BLUE)),
        );
    }

    /**
     * load image file to resize
     *
     * @param $file_location = /path/to/my/file
     */
    public function load($file_location)
    {
        $mime = strtoupper(preg_replace("/.*\.(.*)$/", "\\1", $file_location));

        $this->mime = $mime;
        switch ($this->mime) {
            
            case "PNG":
                $this->thumb = imagecreatefrompng($file_location);
                imagealphablending($this->thumb, false); // setting alpha blending off
                imagesavealpha($this->thumb, true); // save alphablending setting (important)
                break;
            
            case "GIF":
                $this->thumb = imagecreatefromgif($file_location);
                break;
            
            case "JPG":
            case "JPEG":
                $this->thumb = imagecreatefromjpeg($file_location);
                break;
            
            default:
                //die('Not allowed file');
                break;
        }

        $this->sourceWidth = $this->newWidth = imagesx($this->thumb);
        $this->sourceHeight = $this->newHeight = imagesy($this->thumb);
    }

    /**
     * set the width of the thumbnail and calculate the height
     *
     * @param integer $width
     * @param bool $resample
     */
    protected function setWidth($width, $resample = true)
    {
        $this->newWidth = abs(intval($width));
        $this->newHeight = floor(($width / $this->sourceWidth) * $this->sourceHeight);

        if ( $resample === true ) {
            $this->resample();
        }
    }

    /**
     * set the height of the thumbnail and calculate the witdh
     *
     * @param integer $height
     * @param bool $resample
     */
    protected function setHeight($height, $resample = true)
    {
        $this->newHeight = abs(intval($height));
        $this->newWidth = floor(($height / $this->sourceHeight) * $this->sourceWidth);

        if ( $resample === true ) {
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
    public function resample()
    {
        $tmp = $this->thumb;
        $this->thumb = $this->newThumb();

        imagecopyresampled(
            $this->thumb, // (dst_img) Destination image link resource.
            $tmp, // (src_img) Source image link resource.
            0, // (dst_x) x-coordinate of destination point.
            0, // (dst_y) y-coordinate of destination point.
            0, // (src_x) x-coordinate of source point.
            0, // (src_y) y-coordinate of source point.
            $this->newWidth, // (dst_w) Destination width.
            $this->newHeight, // (dst_h) Destination height.
            $this->sourceWidth, // (sourceWidth) Source width.
            $this->sourceHeight // (sourceHeight) Source height.
        );
        unset($tmp);
    }

    /**
     * @param integer $w width
     * @param integer $h height
     * @param bool $cut_from_image
     */
    private function crop($w, $h, $cut_from_image = false)
    {
        if ( $cut_from_image === true ) {
            $this->doSimpleCrop($w, $h);
        } else {
            if ( $this->sourceWidth >= $this->sourceHeight ) {
                if ( ($this->sourceWidth / $this->sourceHeight) > ($w / $h) ) {
                    ( $cut_from_image === true ) ? $this->setHeight($h, false) : $this->setWidth($w, false);
                } else {
                    ( $cut_from_image === true ) ? $this->setWidth($w, false) : $this->setHeight($h, false);
                }
            } else {
                if ( $w >= $h ) {
                    ( $cut_from_image === true ) ? $this->setWidth($w) : $this->setHeight($h, false);
                } else {
                    ( $cut_from_image === true ) ? $this->setHeight($h) : $this->setWidth($w, false);
                }
            }
            $this->resample();
            $this->resampleCrop();
        }
    }

    /**
     * @param integer $w width
     * @param integer $h height
     */
    private function doSimpleCrop($w, $h)
    {
        if ( $this->sourceWidth >= $this->sourceHeight ) {
            if ( ($this->sourceWidth / $this->sourceHeight) > ($w / $h) ) {
                $this->setHeight($h, false);
            } else {
                $this->setWidth($w, false);
            }
        } else {
            if ( $w >= $h ) {
                $this->setWidth($w, false);
            } else {
                $this->setHeight($h, false);
            }
        }
        $this->resample();

        $this->sourceWidth = $this->newWidth;
        $this->sourceHeight = $this->newHeight;

        $this->dst_x = 0;
        $this->dst_y = 0;
        $this->src_x = 0;
        $this->src_y = 0;

        if ( $this->sourceWidth >= $this->sourceHeight ) {
            if ( $this->sourceWidth / $this->sourceHeight > $w / $h ) {
                $this->newWidth = $w;
                $this->dst_x = ($this->sourceWidth - $w) / 2;
            } else {
                $this->newHeight = $h;
                $this->dst_y = ($this->sourceHeight - $h) / 2;
            }
        } else {
            if ( $w >= $h ) {
                $this->newHeight = $h;
                $this->dst_y = ($this->sourceHeight - $h) / 2;
            } else {
                $this->newWidth = $w;
                $this->dst_x = ($this->sourceWidth - $w) / 2;
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
    private function resampleCrop()
    {
        if ( $this->originalWidth > 0
            && $this->originalHeight > 0
            && (
                $this->newWidth != $this->originalWidth
                || $this->newHeight != $this->originalHeight
            )
        ) {
            $tmp = $this->thumb;
            $this->thumb = $this->newThumb($this->originalWidth, $this->originalHeight);

            $_top = 0;
            $_bottom = 0;

            if ( $this->newWidth != $this->originalWidth ) {
                $_top = ($this->originalWidth - $this->newWidth) / 2;
            }
            if ( $this->newHeight != $this->originalHeight ) {
                $_bottom = ($this->originalHeight - $this->newHeight) / 2;
            }
            imagecopy(
                $this->thumb,
                $tmp,
                $_top,
                $_bottom,
                0,
                0,
                $this->newWidth,
                $this->newHeight
            );
        }
    }

    /**
     * generate a new thumbnail with the specified dimensions
     *
     * @param int $w width
     * @param int $h height
     * @return resource|string
     */
    public function newThumb($w = 0, $h = 0)
    {
        $_w = ($w > 0) ? $w : $this->newWidth;
        $_h = ($h > 0) ? $h : $this->newHeight;

        if ( $this->mime == 'PNG' ) {
            $this->thumb = imagecreatetruecolor($_w, $_h);
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

        } else {
            $this->thumb = imagecreatetruecolor($_w, $_h);
            $color = imagecolorallocate(
                $this->thumb,
                $this->backgroundColor[0],
                $this->backgroundColor[1],
                $this->backgroundColor[2]
            );
            imagefill($this->thumb, 10, 10, $color);

        }

        if ( !isset($this->mime) || $this->mime == '' ) {
            $this->mime = 'JPG';
            $this->newWidth = $_w;
            $this->newHeight = $_h;
        }

        return $this->thumb;
    }

    /**
     * @param int $_w width
     * @param int $_h height
     * @param bool $cut_from_image used in crop files if you want to cut from it and center the thumbnail
     */
    public function resize($_w = 0, $_h = 0, $cut_from_image = false)
    {
        $this->originalWidth = $_w;
        $this->originalHeight = $_h;
        if ( $_w > 0 && $_h > 0 ) {
            $this->crop($_w, $_h, $cut_from_image);
        } elseif ( $_w > 0 && $_h <= 0 ) {
            $this->setWidth($_w, true);
        } elseif ( $_h > 0 && $_w <= 0 ) {
            $this->setHeight($_h, true);
        }
    }

    /**
     *
     * save the file to disk
     */
    public function save()
    {
        switch ($this->mime) {

            case "PNG":
                imagepng($this->thumb, $this->target, 0);
                break;

            case "GIF":
                imagegif($this->thumb, $this->target);
                break;

            case "JPG":
            case "JPEG":
                imagejpeg($this->thumb, $this->target, $this->quality);
                break;

            default:
                die('Not allowed file');
                break;
        }
    }

    /**
     * display the image and save the file to disk (optional)
     *
     * @param bool $save
     */
    public function display($save = false)
    {
        switch ($this->mime) {

            case "PNG":
                header("Content-type: image/png");
                if ( $save === true ) {
                    imagepng($this->thumb, $this->target, 0);
                }
                imagepng($this->thumb, NULL, 1);
                break;

            case "GIF":
                header("Content-type: image/gif");
                if ( $save === true ) {
                    imagegif($this->thumb, $this->target);
                }
                imagegif($this->thumb, NULL);
                break;

            case "JPG":
            case "JPEG":
                header("Content-type: image/jpeg");
                if ( $save === true ) {
                    imagejpeg($this->thumb, $this->target, $this->quality);
                }
                imagejpeg($this->thumb, NULL, $this->quality);
                break;

            default:
                die('Not allowed file');
                break;
        }
    }

    /**
     * target file where will be the image saved
     *
     * @param string $file_location
     */
    public function setTarget($file_location)
    {
        $this->target = $file_location;
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

            case "PNG":
                imagepng($this->thumb, NULL, 1);
                break;

            case "GIF":
                imagegif($this->thumb, NULL);
                break;

            case "JPG":
            case "JPEG":
                imagejpeg($this->thumb, NULL, $this->quality);
                break;

            default:

                break;
        }

        $tmp = ob_get_clean();
        if ( is_resource($this->thumb) ) {
            imagedestroy($this->thumb);
        }

        return $tmp;
    }
}
