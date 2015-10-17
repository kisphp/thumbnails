<?php

namespace Kisphp;

class ResizeManager
{
    protected $destinationX = 0;
    protected $destinationY = 0;
    protected $sourceX = 0;
    protected $sourceY = 0;
    protected $sourceWidth = 0;
    protected $sourceHeight = 0;
    protected $opacity = 100;

    public function resizeToCanvas(ImageFileInterface $fileInterface, $newWidth, $newHeight)
    {
        $dest = ImageFile::generateBlankImage($newWidth, $newHeight);

        dump($dest);

        $className = get_class($fileInterface);

        $src = $fileInterface;
        dump($src);

        $dst_x = 50;
        $dst_y = 0;
        $src_x = 0;
        $src_y = 0;
        $src_w = 100;
        $src_h = 200;
        $pct = 100; // opacity

        //imagecopymerge($dest, $src, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct);


    }

    public function prototype()
    {
        $dest = imagecreate(200, 200);
        imagecolorallocate($dest, 255, 255, 255);

        $src = imagecreate(100, 200);
        imagecolorallocate($dest, 0, 0, 0);

        $dst_x = 50;
        $dst_y = 0;
        $src_x = 0;
        $src_y = 0;
        $src_w = 100;
        $src_h = 200;
        $pct = 100; // opacity

        imagecopymerge($dest, $src, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct);

        header("Content-type: image/jpeg");
        imagejpeg($dest, null, 100);
        die;
    }
}
