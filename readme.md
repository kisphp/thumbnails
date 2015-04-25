# Thumbnail Generator

```
#!php

$image = new ImageResizer();
// set where thumbnail will be saved (optional)
$image->setTarget('/path/to/thumbnail/file.jpg');
// load original image file
$image->load('/path/to/image/file.jpg');

// show image
$image->display();

// show image and save
$image->display(true);

```


