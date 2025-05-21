<?php
// Create a 400x400 image with a light gray background
$width = 400;
$height = 400;
$image = imagecreatetruecolor($width, $height);

// Colors
$bg_color = imagecolorallocate($image, 243, 238, 240); // Light gray background
$text_color = imagecolorallocate($image, 100, 100, 100); // Dark gray text

// Fill the background
imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

// Add text
$text = "Category Image";
$font_size = 5;
$text_width = imagefontwidth($font_size) * strlen($text);
$text_height = imagefontheight($font_size);

// Center the text
$center_x = ($width - $text_width) / 2;
$center_y = ($height - $text_height) / 2;

imagestring($image, $font_size, $center_x, $center_y, $text, $text_color);

// Output the image as a JPEG
header('Content-Type: image/jpeg');
imagejpeg($image, 'category-placeholder.jpg', 90);

// Free memory
imagedestroy($image);

echo "Placeholder image created successfully.";
?> 