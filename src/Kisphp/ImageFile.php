<?php

namespace Kisphp;

class ImageFile
{
    const EXTENSION_JPG = 'JPG';
    const EXTENSION_PNG = 'PNG';
    const EXTENSION_GIG = 'GIF';

    protected $image;

    /**
     * @var string
     */
    protected $source;

    /**
     * @var int
     */
    protected $width;

    /**
     * @var int
     */
    protected $height;

    /**
     * @var string
     */
    protected $mimeType;

    /**
     * @var int
     */
    protected $newWidth;

    /**
     * @var int
     */
    protected $newHeight;

    public function __construct($sourceFileName)
    {
        $extension = $this->getFileExtensionByName($sourceFileName);

        var_dump($sourceFileName);
        var_dump($extension);
        $image = imagecreatefromjpeg($sourceFileName);

        var_dump($image);

        die;

        $this->source = $source;
    }

    /**
     * @param $sourceFileName
     *
     * @return string
     */
    protected function getFileExtensionByName($sourceFileName)
    {
        $nameParts = explode('.', $sourceFileName);
        $extension = mb_convert_case(end($nameParts), CASE_LOWER, 'UTF-8');

        return $extension;
    }
}
