# Thumbnail Generator

This class will help you to easily resize images and save them to disc or show them to user

## Usage
```php
<?php

$image = new ImageResizer();

// set default background color (here will be red, default is white)
$image->setBackgroundColor(255, 0, 0);

// set where thumbnail will be saved (optional)
$image->setTarget('/path/to/thumbnail/file.jpg');
// load original image file
$image->load('/path/to/image/file.jpg');

// resize image to a 300px width and dynamic height by aspect ratio 
$image->resize(300, 0);

// resize image to a 300px height and dynamic width by aspect ratio 
$image->resize(0, 300);

// usage
$image->resize(new_width, new_height, crop_image=true|false);

// show image
$image->display();

// show image and save
$image->display(true);

```


