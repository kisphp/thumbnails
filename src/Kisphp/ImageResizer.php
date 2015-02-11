<?php

namespace Kisphp\Images;

class ImageResizer extends Helper
{
    public      $quality    = 85;
    public      $mime       = '';
    public      $thumb      = '';
    public      $target     = '';
    protected   $src_w      = 0;
    protected   $src_h      = 0;
    private     $orig_w     = 0;
    private     $orig_h     = 0;
    private     $new_w      = 0;
    private     $new_h      = 0;
    
    /**
     * set the quality of the resulted thumbnail
     */
    public function __construct($quality=85)
    {
        $this->quality = max(1, min(100, abs(intval($quality))));
    }
    
    /**
     * load file to manipulate
     * @file_location = /path/to/my/file
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
        $this->src_w = $this->new_w = imagesx($this->thumb);
        $this->src_h = $this->new_h = imagesy($this->thumb);
    }
    
    /**
     * set the wirth of the thumbnail and calculate the height
     */
    public function setW($w, $resample=true)
    {
        $this->new_w = abs(intval($w));
        $this->new_h = floor(($w / $this->src_w) * $this->src_h);
        if ($resample === true) $this->resample(); 
    }
    
    /**
     * set the height of the thumbnail and calculate the witdh
     */
    public function setH($h, $resample=true)
    {
        $this->new_h = abs(intval($h));
        $this->new_w = floor(($h / $this->src_h) * $this->src_w);
        if ($resample === true) $this->resample(); 
    }
    
    /**
     * resample the image
     */
    public function resample()
    {
        $tmp = $this->thumb;
        $this->thumb = $this->new_thumb();

        imagecopyresampled(
            $this->thumb, // (dst_img) Destination image link resource.
            $tmp, // (src_img) Source image link resource.
            0, // (dst_x) x-coordinate of destination point.
            0, // (dst_y) y-coordinate of destination point.
            0, // (src_x) x-coordinate of source point.
            0, // (src_y) y-coordinate of source point.
            $this->new_w, // (dst_w) Destination width.
            $this->new_h, // (dst_h) Destination height.
            $this->src_w, // (src_w) Source width.
            $this->src_h // (src_h) Source height.
        );
    }
    
    private function crop($w,$h, $cut_from_image = false)
    {
        if ( $cut_from_image === true ) {
            $this->do_simple_crop($w, $h);
        } else {
            if ( $this->src_w >= $this->src_h ) {
                if ( ($this->src_w / $this->src_h) > ($w / $h) )
                {
                    ( $cut_from_image === true ) ? $this->setH($h, false) : $this->setW($w, false);
                } 
                else 
                {
                    ( $cut_from_image === true ) ? $this->setW($w, false) : $this->setH($h, false);
                }
            } 
            else 
            {
                if ( $w >= $h )
                {
                    ( $cut_from_image === true ) ? $this->setW($w) : $this->setH($h, false);
                } 
                else 
                {
                    ( $cut_from_image === true ) ? $this->setH($h) : $this->setW($w, false);
                }
            }
            $this->resample();
            $this->resample_crop();
        }
    }
    
    private function do_simple_crop($w, $h)
    {
        if ( $this->src_w >= $this->src_h ) {
            if ( ($this->src_w / $this->src_h ) > ($w / $h)) {
                $this->setH($h, false);
            } else {
                $this->setW($w, false);
            }
        } else {
            if ( $w >= $h ) {
                $this->setW($w, false);
            } else {
                $this->setH($h, false);
            }
        }
        $this->resample();
        
        $this->src_w = $this->new_w;
        $this->src_h = $this->new_h;

        $this->dst_x = 0;
        $this->dst_y = 0;
        $this->src_x = 0;
        $this->src_y = 0;
        if ( $this->src_w >= $this->src_h ) {
            if ( $this->src_w / $this->src_h > $w / $h ) {
                $this->new_w = $w;
                $this->dst_x = ($this->src_w - $w) / 2;
            } else {
                $this->new_h = $h;
                $this->dst_y = ($this->src_h - $h) / 2;
            }
        } else {
            if ( $w >= $h ) {
                $this->new_h = $h;
                $this->dst_y = ($this->src_h - $h) / 2;
            } else {
                $this->new_w = $w;
                $this->dst_x = ($this->src_w - $w) / 2;
            }
        }
        
        $tmp = $this->thumb;
        $this->thumb = $this->new_thumb();
        
        imagecopy($this->thumb, // (dst_img) Destination image link resource.
            $tmp, // (src_img) Source image link resource.
            $this->src_x, // (src_x) x-coordinate of source point.
            $this->src_y, // (src_y) y-coordinate of source point.
            $this->dst_x, // (src_x) x-coordinate of source point.
            $this->dst_y, // (src_y) y-coordinate of source point.
            $this->src_w, // (src_w) Source width.
            $this->src_h// (src_h) Source height.
        );
    }
    
    /**
     * resize the image and if it is below the requested dimensions puts a blank image below and merges them
     */
    private function resample_crop()
    {
        if ( $this->orig_w > 0
            && $this->orig_h > 0
            && (
                $this->new_w != $this->orig_w
                || $this->new_h != $this->orig_h
            )
        ) {
            $tmp = $this->thumb;
            $this->thumb = $this->new_thumb($this->orig_w, $this->orig_h);
            
            $_top = 0;
            $_bottom = 0;
            
            if ( $this->new_w != $this->orig_w ) {
                $_top = ($this->orig_w - $this->new_w) / 2;
            }
            if ( $this->new_h != $this->orig_h ) {
                $_bottom = ($this->orig_h - $this->new_h) / 2;
            }
            imagecopy(
                $this->thumb,
                $tmp,
                $_top,
                $_bottom,
                0,
                0,
                $this->new_w,
                $this->new_h
            );
        }
    }
    
    /**
     * generate a new thumbnail with the specified dimensions
     * $w = width
     * $h = height
     */
    public function new_thumb($w=0, $h=0)
    {
        $_w = ($w > 0) ? $w : $this->new_w;
        $_h = ($h > 0) ? $h : $this->new_h;
        if ( $this->mime == 'PNG' ) {
            $this->thumb = imagecreatetruecolor($_w, $_h);
            $color = imagecolorallocate($this->thumb, 255, 255, 255);
            imagefill($this->thumb, 0, 0, $color);
            imagealphablending($this->thumb, false); // setting alpha blending off
            imagesavealpha($this->thumb, true); // save alphablending setting (important)
        } else {
            $this->thumb = imagecreatetruecolor($_w, $_h);
            $color = imagecolorallocate($this->thumb, 255, 255, 255);
            imagefill($this->thumb, 10, 10, $color);
            
        }
        if ( ! isset($this->mime) || $this->mime == '' ) {
            $this->mime = 'JPG';
            $this->new_w = $_w;
            $this->new_h = $_h;
        }
        return $this->thumb;
    }
    
    /**
     * this is a controller that decide how to run your code
     * $_w = width
     * $_h = height
     * $cut_from_image = used in crop files if you want to cut from it and center the thumbnail
     */
    public function resize($_w=0, $_h=0, $cut_from_image = false)
    {
        $this->orig_w = $_w;
        $this->orig_h = $_h;
        if ( $_w > 0 && $_h > 0 ) {
            $this->crop($_w, $_h, $cut_from_image);
        } elseif ( $_w > 0 && $_h <= 0 ) {
            $this->setW($_w, true);
        } elseif ( $_h > 0 && $_w <= 0 ) {
            $this->setH($_h, true);
        } else {
            
        }
    }
    
    /**
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
     */
    public function display($save=false)
    {
        //printr($this); die();
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
     */
    public function target($file_location)
    {
        $this->target = $file_location;
    }
    
    /**
     * return the image string so you will be able to save it into a file
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
