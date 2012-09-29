<?php
/**
 * This file holds the landing page for FaceProject
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

require_once 'app/FaceProject.php';

$imageFile = 'test.jpg';

$faceProject = new FaceProject();
$faceProject->setImage($imageFile);
$faceProject->outputImage();
