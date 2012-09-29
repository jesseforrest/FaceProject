<?php
/**
 * This file holds the FaceProject class
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

/**
 * This class provides functionality to convert an image into a FaceProject 
 * JPG image
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
class FaceProject
{
   /**
    * The number of pixels to add as a border around the image
    * 
    * @var integer
    */
   const BORDER_PIXEL_SIZE = 12;
   
   /**
    * The new image resource
    * 
    * @var resource|null
    */
   protected $newImage = null;
   
   /**
    * The original image resource
    *
    * @var resource|null
    */
   protected $originalImage = null;
   
   /**
    * The width of the image
    * 
    * @var integer|null
    */
   protected $width = null;

   /**
    * The height of the image
    *
    * @var integer|null
    */
   protected $height = null;

   /**
    * Sets the image resource based on the file path
    * 
    * @param string $path The path to the image
    * 
    * @return boolean Returns true on success or false otherwise
    */
   public function setImage($path)
   {
      $this->originalImage = @imagecreatefromjpeg($path);
      if (!$this->originalImage)
      {
         return false;
      }
      
      list($width, $height, $type, $attr) = getimagesize($path);
      
      // Create new image with large enough canvas to hold reflection
      $this->newImage = imagecreatetruecolor(
         $width,
         $height);
      
      // Append original image to the top of the canvas
      imagecopy(
         $this->newImage, 
         $this->originalImage, 
         0, 
         0, 
         0, 
         0, 
         $width, 
         $height);
      
      $this->setWidth($width);
      $this->setHeight($height);      
            
      return true;
   }
   
   /**
    * Adds the reflection to the bottom of the image
    * 
    * @return void
    */
   protected function addReflection()
   {
      $width = $this->width;
      $height = $this->height;
      
      // Create new image with large enough canvas to hold reflection
      $tempImage = imagecreatetruecolor(
         $width,
         $height + self::BORDER_PIXEL_SIZE);
      
      // Append original image to the top of the canvas
      imagecopy($tempImage, $this->newImage, 0, 0, 0, 0, $width, $height);
      
      // Copy bottom portion of original image (to be used for reflection)
      $reflectPiece = imagecreatetruecolor($width, self::BORDER_PIXEL_SIZE);
      imagecopy(
         $reflectPiece,
         $tempImage,
         0,
         0,
         0,
         $height - self::BORDER_PIXEL_SIZE,
         $width,
         self::BORDER_PIXEL_SIZE);
      
      // Flip the copied reflection part vertically
      $reflectPiece = $this->flip($reflectPiece, true, false);
            
      // Append copied reflection part at bottom of canvas
      imagecopy(
         $tempImage,
         $reflectPiece,
         0,
         $height,
         0,
         0,
         $width,
         self::BORDER_PIXEL_SIZE);
      
      // Convert light pixels directly to white
      for ($y = $this->height; $y < $this->height + self::BORDER_PIXEL_SIZE; $y++)
      {
         $line1x1 = 0;
         $line1x2 = null;
         $line2x1 = null;
         $line2x2 = $this->width - 1;
         for ($x = 0; $x < $this->width; $x++)
         {
            $rgb = imagecolorat($tempImage, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;

            // If should be whitened
            if ((abs($r - $g) > 30) ||
                  (abs($r - $b) > 30) ||
                  (abs($g - $b) > 30))
            {
               if ($line1x2 === null)
               {
                  $line1x2 = $x;
               }
               $line2x1 = $x;
            }
         }
         
         // If first line, output white
         if ($y == $this->height)
         {
            $white = imagecolorallocate($tempImage, 255, 255, 255);
            imageline(
               $tempImage, 
               self::BORDER_PIXEL_SIZE - 1, 
               $y, 
               $line1x2, 
               $y, 
               $white);
            imageline(
               $tempImage, 
               $line2x1, 
               $y, 
               $line2x2 - self::BORDER_PIXEL_SIZE, 
               $y, 
               $white);
         }
         else
         {
            $black = imagecolorallocate($tempImage, 0, 0, 0);
            imageline($tempImage, $line1x1, $y, $line1x2, $y, $black);
            imageline($tempImage, $line2x1, $y, $line2x2, $y, $black);
         }
      }
      
      $padding = self::BORDER_PIXEL_SIZE;
      $white = imagecolorallocate($tempImage, 255, 255, 255);
      $black = imagecolorallocate($tempImage, 0, 0, 0);
      
      // Top black border
      for ($i = 0; $i < $padding - 1; $i++)
      {
         imageline($tempImage, 0, $i, $this->width - 1, $i, $black);
      }
      imageline(
         $tempImage, 
         0, 
         $padding - 1, 
         $this->width - 1, 
         $padding - 1, 
         $white);
      
      // Left black border
      for ($i = 0; $i < $padding - 1; $i++)
      {
         imageline($tempImage, $i, 0, $i, $this->height, $black);
      }
      imageline(
         $tempImage, 
         $padding - 1, 
         $padding - 1, 
         $padding - 1, 
         $this->height - 1, 
         $white);

      // Right black border
      for ($i = 0; $i < $padding - 1; $i++)
      {
         imageline(
            $tempImage, 
            $this->width - 1 - $i, 
            0, 
            $this->width - 1 - $i, 
            $this->height, 
            $black);
      }
      imageline(
         $tempImage,
         $this->width - $padding,
         $padding - 1,
         $this->width - $padding,
         $this->height - 1,
         $white);
      
      $this->newImage = $tempImage;
      
   }
   
   /**
    * Holds the width, in pixels, of the image.  
    * 
    * @param integer|null $width The width, in pixels, of the image
    * 
    * @return void
    */
   public function setWidth($width)
   {
      $this->width = $width;
   }

   /**
    * Holds the height, in pixels, of the image. 
    *
    * @param integer|null $height The height, in pixels, of the image
    *
    * @return void
    */
   public function setHeight($height)
   {
      $this->height = $height;
   }
   
   /**
    * This function expects the image to be in greyscale already
    * 
    * @return array An associative array of allocated colors
    */
   public function getAllocatedColors()
   {
      $lightest = 0;
      $darkest = 255;
      $average = 0;
      for ($x = 0; $x < $this->width; $x++)
      {
         for ($y = 0; $y < $this->height; $y++)
         {
            $rgb = imagecolorat($this->newImage, $x, $y);
            $r = ($rgb >> 16) & 0xFF;

            if ($r > $lightest)
            {
               $lightest = $r;
            }
            if ($r < $darkest)
            {
               $darkest = $r;
            }
            $average += $r;
         }
      }
      $average = intval($average / ($this->width * $this->height)); 
      
      return array(
         'lightest_rgb' => $lightest,
         'darkest_rgb' => $darkest,
         'average_rgb' => $average
      );
   }
   
   /**
    * Flips the image
    *
    * @param resource $image      The image to flip
    * @param boolean  $vertical   Whether to flip the image vertically
    * @param boolean  $horizontal Whether ot flip the image horizontally
    *
    * @return resource Returns the new image
    */
   protected function flip($image, $vertical, $horizontal)
   {
      $w = imagesx($image);
      $h = imagesy($image);
       
      if (!$vertical && !$horizontal)
      {
         return $image;
      }
       
      $flipped = imagecreatetruecolor($w, $h);
       
      if ($vertical)
      {
         for ($y=0; $y < $h; $y++)
         {
            imagecopy($flipped, $image, 0, $y, 0, $h - $y - 1, $w, 1);
         }
      }
       
      if ($horizontal)
      {
         if ($vertical)
         {
            $image = $flipped;
            $flipped = imagecreatetruecolor($w, $h);
         }
          
         for ($x=0; $x < $w; $x++)
         {
            imagecopy($flipped, $image, $x, 0, $w - $x - 1, 0, 1, $h);
         }
      }
      return $flipped;
   }
   
   /**
    * This will output the image in PNG format
    * 
    * @return void
    */
   public function outputImage()
   {      
      // Return if could not create image resource
      if ($this->newImage === false)
      {
         return;
      }
      
      // Convert to grey
      imagefilter($this->newImage, IMG_FILTER_GRAYSCALE);
      
      // Create colors
      $colors = $this->getAllocatedColors();
      
      // Convert light pixels directly to white
      for ($x = 0; $x < $this->width; $x++)
      {
         for ($y = 0; $y < $this->height; $y++)
         {
            $rgb = imagecolorat($this->newImage, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            
            // If whiten
            if ($r > $colors['average_rgb'] + 50)
            {
               $r = $r + 15;
               $r = ($r > 255) ? 255 : $r;
               $lighter = imagecolorallocate($this->newImage, $r, $r, $r);
               imagesetpixel($this->newImage, $x, $y, $lighter);
               $gray = $r;
               
               // Get colors from original image
               $rgb = imagecolorat($this->originalImage, $x, $y);
               $r = ($rgb >> 16) & 0xFF;
               $g = ($rgb >> 8) & 0xFF;
               $b = $rgb & 0xFF;
               
               // If coordinate was colorful
               if ((abs($r - $g) > 50) || 
                  (abs($r - $b) > 50) || 
                  (abs($g - $b) > 50))
               {
                  $r = ($r + 10 > 255) ? 255 : $r + 10;
                  $g = ($g + 10 > 255) ? 255 : $g + 10;
                  $b = ($b + 10 > 255) ? 255 : $b + 10;
                  if (($r >= $g) && ($r >= $b))
                  {
                     $r = ($gray + 10 > 255) ? 255 : $gray + 10;
                     $g = $gray;
                     $b = $gray;
                  }
                  else if (($g >= $r) && ($g >= $b))
                  {
                     $r = $gray;
                     $g = ($gray + 10 > 255) ? 255 : $gray + 10;
                     $b = $gray;
                  }
                  else if (($b >= $r) && ($b >= $g))
                  {
                     $r = $gray;
                     $g = $gray;
                     $b = ($gray + 10 > 255) ? 255 : $gray + 10;
                  }
                  $colorful = imagecolorallocate($this->newImage, $r, $g, $b);
                  imagesetpixel($this->newImage, $x, $y, $colorful);
               }
            }
            // If darken
            else if ($r < $colors['average_rgb'])
            {               
               // Get colors from original image
               $rgb = imagecolorat($this->originalImage, $x, $y);
               $r = ($rgb >> 16) & 0xFF;
               $g = ($rgb >> 8) & 0xFF;
               $b = $rgb & 0xFF;
               
               // Darken colors
               $r = ($r - 10 < 0) ? 0 : $r - 10;
               $g = ($g - 10 < 0) ? 0 : $g - 10;
               $b = ($b - 10 < 0) ? 0 : $b - 10;
               $darker = imagecolorallocate($this->newImage, $r, $g, $b);
               imagesetpixel($this->newImage, $x, $y, $darker);
            }
         }
      }
      
      // Add the reflection
      $this->addReflection();
      
      // Output header
      header('Content-type: image/jpg');
      
      // Output the image
      imagejpeg($this->newImage, null, 100);
      
      // Destroy the image
      imagedestroy($this->newImage);
   }
}
