<?php
/**
 * This file shows an example of how to use the FaceProject 
 * library
 *
 * PHP Version 5
 *
 * @category  FaceProject
 * @package   JesseForrest
 * @author    Jesse Forrest <jesseforrest@gmail.com>
 * @copyright 2012 Jesse Forrest
 * @license   MIT License (MIT)
 * @link      https://github.com/jesseforrest/FaceProject
 * @filesource
 */

require_once '../FaceProject.php';

$imageFile = 'example.jpg';

$faceProject = new FaceProject();
$faceProject->setImage($imageFile);
$faceProject->outputImage();
