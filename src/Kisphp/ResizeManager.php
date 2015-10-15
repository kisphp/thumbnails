<?php

namespace Kisphp;

class ResizeManager
{

    public function getResizeDimensions($sourceWidth, $sourceHeight, $wantedWidth, $wantedHeight)
    {
        if ($sourceWidth <= $sourceHeight) {
            $sourceRatio = $sourceWidth / $sourceHeight;
            $targetRatio = $wantedWidth / $wantedHeight;
            if ($sourceRatio > $targetRatio) {
                //$setHeight($height, false);
                return 'a';
            } else {
                //$setWidth($width, false);
                return 'b';
            }
        } else {
            if ($wantedWidth >= $wantedHeight) {
                return 'c';
                //$setWidth($width, false);
            } else {
                return 'd';
                //$setHeight($height, false);
            }
        }
    }
}

// sw = 1920, sh = 2560 => tw = 270, th = 300

// sw = 270, sh = 365 => tw = 270, th = 300 (RESIZE)

// sw = 270, sh = 300 (CROP)
