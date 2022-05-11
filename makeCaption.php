<?php
/**

HOW TO USE THIS FILE:

URL parameters this file accepts:
photo : full file name (not path), including .jpg extension, of a JPEG in the images/ folder.  Must be a JPEG.
caption1 : text for the top caption (optional)
caption2 : text for the bottom caption 
width : width of final PNG (optional).  If not specified, BOTH image dimensions will be calculated from original photo dimensions.
height : height of final PNG (optional).  If not specified, BOTH image dimensions will be calculated from original photo dimensions.

example: makeCaption.php?photo=el32p.jpg&caption1=foo&caption2=bar&width=360&height=240
The above URL will make a thumbnail version of the original meme image.

*/

// ******** HELPER FUNCTIONS ************

/**
 * Writes the given text with a border into the image using TrueType fonts.
 * @author John Ciacia 
 * @param image An image resource
 * @param size The font size
 * @param angle The angle in degrees to rotate the text
 * @param x Upper left corner of the text
 * @param y Lower left corner of the text
 * @param textcolor This is the color of the main text
 * @param strokecolor This is the color of the text border
 * @param fontfile The path to the TrueType font you wish to use
 * @param text The text string in UTF-8 encoding
 * @param px Number of pixels the text border will be
 * @see http://us.php.net/manual/en/function.imagettftext.php
 */
function imagettfstroketext(&$image, $size, $angle, $x, $y, &$textcolor, &$strokecolor, $fontfile, $text, $px) {
 
    for($c1 = ($x-abs($px)); $c1 <= ($x+abs($px)); $c1++)
        for($c2 = ($y-abs($px)); $c2 <= ($y+abs($px)); $c2++)
            $bg = imagettftext($image, $size, $angle, $c1, $c2, $strokecolor, $fontfile, $text);
 
   return imagettftext($image, $size, $angle, $x, $y, $textcolor, $fontfile, $text);
}
/**
 * Given a maximum width and height, a font, and text, calculates and returns
 * an appropriate font size that reasonably fills the width without being too tall.
 * It also alters the $textHeight parameter to the height of the text's bounding box.
 * @author Valerie Green
 * @param maxWidth Maximum width
 * @param maxHeight Maximum height
 * @param fontFile Path to the TTF file
 * @param text Text string that will be displayed
 * @param textHeight if supplied, when the function returns, contains the height of the text's bounding box
 */
function calcFontSize($maxWidth, $maxHeight, $fontFile, $text, &$textHeight=null) {
	$fontSize = 100; // start at default
	$textHeight = 0; // default
	
	$bbox = imagettfbbox($fontSize, 0, $fontFile, $text);
	if ($bbox) {
		$scaleSlop = 25;
		$scaleFactorX = ($maxWidth-$scaleSlop) / ($bbox[2] - $bbox[6]);
		// now multiply the font size by the scale factor, so it is close to fitting the image width.
		// check the height, too, though, and limit it to 25% of the overall height...glargh
		$scaleFactorY = ($maxHeight / 4 ) / ($bbox[3] - $bbox[7]);
		$scaleFactor = min($scaleFactorX, $scaleFactorY);
		$fontSize *= $scaleFactor;
		$textHeight = ($bbox[3] - $bbox[7]) * $scaleFactor;
	}
	
	return $fontSize;
}

// vgreen: the following code checks the address bar for an image path and a caption.
// The top caption is optional.
// It loads up the image file using imagecreatefromjpeg (so you must supply an image in JPEG format)
// It then creates an image with the caption text.
// Finally, it copies the caption on top of the image.

$im = null;
if (isset($_GET['photo'])) {
	$photo = $_GET['photo'];
	// Load the photo.  It must be in the images folder.
	$im = imagecreatefromjpeg('assets/images/' . $photo);
}

// default text to draw on the bottom
$text_bottom = 'Your caption here...';
$text_top = "";

// see if there's a bottom and/or top caption supplied

if (isset($_GET['caption2'])) {
	$text_bottom = $_GET['caption2'];
}
if (isset($_GET['caption1'])) {
	$text_top = $_GET['caption1'];
	// tbd: upper case?
	$text_top = strtoupper($text_top);
}

// default image size if we can't figure out the size
$imageWidth = 800;
$imageHeight = 600;

if ($im) {
	$imageWidth = imagesx($im);
	$imageHeight = imagesy($im);
}

$font = 'assets/fonts/Impact.ttf';

// for each caption, calculate the font size, based on the maximum width and height.
$fontHeight = 0;
$fontSizeTop = calcFontSize($imageWidth, $imageHeight, $font, $text_top, $fontHeight);
$fontSizeBottom = calcFontSize($imageWidth, $imageHeight, $font, $text_bottom);

// add some slop for the border size and margins
$borderSize = 4;
$baselineSlop = 15; // this is because the ttf size calculation is buggy in PHP
$LRSlop = 0;
$angle = 0;
$xOffset = $borderSize + $LRSlop;
$yOffset = $imageHeight-$borderSize-$baselineSlop;


// Create the image for the font
$fontImage = imagecreatetruecolor($imageWidth, $imageHeight);
// fill the background, and call it transparent (all bits "on" in the alpha channel)
$transparent = 0xff000000;
imagefill($fontImage, 0, 0, $transparent);

// colors for text:
$black = 0;
$white = 0xffffff;

if ($im === null) {
	$im = imagecreatetruecolor($imageWidth, $imageHeight);
	$bgColor = 0xffff00;
	imagefilledrectangle($im, 0, 0, $imageWidth-1, $imageHeight-1, $bgColor);
}

// call John's awesome function for each caption!
imagettfstroketext($fontImage, $fontSizeBottom, $angle, $xOffset, $yOffset, $white, $black, $font, $text_bottom, $borderSize);
// reset the offset for the top caption
if (strlen($text_top) != 0) {
	$yOffset = $fontHeight + $borderSize + $baselineSlop;	
	imagettfstroketext($fontImage, $fontSizeTop, $angle, $xOffset, $yOffset, $white, $black, $font, $text_top, $borderSize);
}

// smoosh the text onto the photo
// Set the margins for the caption image and get the height/width of the caption image
// Note: they should be the same dimensions
$lr_margin = 5;
$marge_bottom = 0;
$sx = $imageWidth;
$sy = $imageHeight;

// Copy the caption image onto our photo using the margin offsets and the photo 
// width to calculate positioning of the caption. 
imagecopy($im, $fontImage, 0 + $lr_margin, $imageHeight - $sy - $marge_bottom, 0, 0, $sx, $sy);

// Set the content-type
header('Content-Type: image/png');

// if a specific height and width are set, resize the image.  Otherwise, use the original.
if (isset($_GET['width']) && isset($_GET['height'])) {
	$scaleW = $_GET['width'] * 1.0;  // hey PHP, these are ints, honest!  :-)
	$scaleH = $_GET['height'] * 1.0;
	$scaledIm = imagecreatetruecolor($scaleW, $scaleH);
	imagecopyresized($scaledIm, $im, 0, 0, 0, 0, $scaleW, $scaleH, $imageWidth, $imageHeight);
	imagepng($scaledIm);
	imagedestroy($scaledIm); // free memory
}
else {
	// Using imagepng() results in clearer text compared with imagejpeg()
	imagepng($im);
	imagedestroy($im); // free memory
}
// free the memory that was allocated.
imagedestroy($fontImage);

?>