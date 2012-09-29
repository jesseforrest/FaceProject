FaceProject
===========

This is a PHP library that converts a photo of a persons face into a unique
stylized JPG image.  

Overview
--------

The only file that needs to be included in order to utilize FaceProject is the
FaceProject.php file.  We additionally include an *example* directory to show 
a common use case.

Requirements
------------

Your version of PHP will need to be compiled with the following library:
 - GD: Supports necessary image functions
 
 Example - Basic Usage
---------------------

This example will show how to select the image to be used and output an image.

```php
<?php
require_once 'FaceProject.php';
$imageFile = 'example.jpg';
$faceProject = new FaceProject();
$faceProject->setImage($imageFile);
$faceProject->outputImage();
```