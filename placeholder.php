<?php
/**
 * PASTIMES — images/placeholder.php
 * Generates a placeholder image dynamically
 */
header('Content-Type: image/png');
$width  = 400;
$height = 300;
$img    = imagecreatetruecolor($width, $height);
$bg     = imagecolorallocate($img, 15, 31, 68);
$gold   = imagecolorallocate($img, 201, 168, 76);
$gray   = imagecolorallocate($img, 74, 64, 48);

imagefill($img, 0, 0, $bg);
imagerectangle($img, 2, 2, $width-3, $height-3, $gray);

// Shirt icon lines
$cx = $width/2; $cy = $height/2;
imageline($img, $cx-40, $cy-30, $cx-60, $cy-50, $gold);
imageline($img, $cx-60, $cy-50, $cx-80, $cy-30, $gold);
imageline($img, $cx-80, $cy-30, $cx-80, $cy+40, $gold);
imageline($img, $cx-80, $cy+40, $cx+80, $cy+40, $gold);
imageline($img, $cx+80, $cy+40, $cx+80, $cy-30, $gold);
imageline($img, $cx+80, $cy-30, $cx+60, $cy-50, $gold);
imageline($img, $cx+60, $cy-50, $cx+40, $cy-30, $gold);
imageline($img, $cx+40, $cy-30, $cx+20, $cy-10, $gold);
imageline($img, $cx+20, $cy-10, $cx-20, $cy-10, $gold);
imageline($img, $cx-20, $cy-10, $cx-40, $cy-30, $gold);

imagestring($img, 3, $cx-40, $cy+55, 'No Image', $gray);

imagepng($img);
imagedestroy($img);
?>