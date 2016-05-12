# KISPHP Thumbnail Generator

[![Build Status](https://travis-ci.org/kisphp/thumbnails.svg?branch=master)](https://travis-ci.org/kisphp/thumbnails)
[![codecov.io](https://codecov.io/github/kisphp/thumbnails/coverage.svg?branch=master)](https://codecov.io/github/kisphp/thumbnails?branch=master)

[![Latest Stable Version](https://poser.pugx.org/kisphp/thumbnails/v/stable)](https://packagist.org/packages/kisphp/thumbnails)
[![Total Downloads](https://poser.pugx.org/kisphp/thumbnails/downloads)](https://packagist.org/packages/kisphp/thumbnails)
[![License](https://poser.pugx.org/kisphp/thumbnails/license)](https://packagist.org/packages/kisphp/thumbnails)
[![Monthly Downloads](https://poser.pugx.org/kisphp/thumbnails/d/monthly)](https://packagist.org/packages/kisphp/thumbnails)


This class will help you to easily resize images and save them to disc or show them to user

## Installation

```bash
composer require kisphp/thumbnails
```

Then add make sure you load composer autoloader:

```php
require_once 'path/to/vendor/autoload.php';
```


## Usage
```php
<?php

require_once 'path/to/vendor/autoload.php';

$image = new \Kisphp\ImageResizer();

// load original image file
$image->load('/path/to/image/file.jpg');

// set where thumbnail will be saved (optional)
$image->setTarget('/path/to/thumbnail/file.jpg');

// resize image to a 300px width and dynamic height by aspect ratio 
$image->resize(300, 0);

// or
// resize image to a 300px height and dynamic width by aspect ratio 
$image->resize(0, 300);

// show image and save
$image->display(true);
```

#### Change thumbnail background color
> If you crop the images, you can use a custom background color to integrate the thumbnail into your design

```php
// set default background color (here will be red, default is white)
$image->setBackgroundColor(255, 0, 0);
```

#### Resize method usage
```php
$image->resize(new_width, new_height, crop_image=true|false);
```

#### Show image without saving it
```php
$image->display();
```

> Note that this methods outputs `header('Content-Type: image/..mime-type..')`

