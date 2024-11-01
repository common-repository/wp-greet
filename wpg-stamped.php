<?php
/* This file is part of the wp-greet plugin for WordPress */

/*
  Copyright 2008-2016  Hans Matzen  (email : webmaster at tuxlog dot de)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
// if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

// get parameter
$cardimg_url  = $_GET['cci'];
$stampimg_url = $_GET['sti'];
$stampwidth   = $_GET['stw'];
$stampopacity = intval( $_GET['sto'] );

if ( $stampopacity == '' ) {
	$stampopacity = 0;
}

$ob = ( isset( $_GET['ob'] ) && $_GET['ob'] == 1 ? 1 : 0 );

// wenn direkter aufruf, dann header ausgeben
if ( $ob != 1 ) {
	header( 'Content-type: image/png' );
}


// Bilder laden
$i1 = file_get_contents( $cardimg_url );
$i2 = file_get_contents( $stampimg_url );

$imgsrc     = imagecreatefromstring( $i1 );
$imgzeichen = imagecreatefromstring( $i2 );

// set alpha in any case
imagealphablending( $imgsrc, 1 );
imagealphablending( $imgzeichen, 1 );

// Hat die Briefmarke transparenz
$stamp_transparent = false;
if ( check_transparent( $imgzeichen ) ) {
	$stamp_transparent = true;
}


// Bild Infos
$width  = imagesx( $imgsrc );  // Höhe Hauptbild
$height = imagesy( $imgsrc );  // Breite Hauptbild

$x = imagesx( $imgzeichen ); // Höhe Bild Briefmarke
$y = imagesy( $imgzeichen ); // Breite Bild Briefmarke

// neues Bild erzeugen
$img = imagecreatetruecolor( $width, $height );

// set alphachannelmerge all images
imagealphablending( $s, true );

// Postkarte in neues Bild einfügen
imagecopy( $img, $imgsrc, 0, 0, 0, 0, $width, $height );

// Breite und Höhe der Marke berechnen
$newx = (int) ( $width * $stampwidth / 100.0 );
$newy = (int) ( $y * ( $newx / $x ) );

// Briefmarke einfügen
$abstand_links = $width - $newx + 1;
$abstand_oben  = 1;

$imgzeichen = imagescale( $imgzeichen, $newx, $newy );
$w          = imagesx( $imgzeichen );
$h          = imagesy( $imgzeichen );

if ( ! $stamp_transparent ) {
	imagecopymerge( $img, $imgzeichen, $abstand_links, $abstand_oben, 0, 0, $w, $h, $stampopacity );
} else {
	imagefilter( $imgzeichen, IMG_FILTER_BRIGHTNESS, 255 - intval( $stampopacity * 2.5 ) );
	$img = imagemergealpha( $img, $imgzeichen, $abstand_links, $abstand_oben, $stampopacity );
}

// wenn aufruf zur rückgabe als string, dann output umleiten
if ( $ob == 1 ) {
	ob_start();
	// Bild anzeigen
	imagepng( $img );
	$out = ob_get_contents();
	ob_end_clean();
	echo $out;
} else {
	imagepng( $img );
}

// Speicher freigeben
imagedestroy( $img );
imagedestroy( $imgsrc );
imagedestroy( $imgzeichen );

//
// Functions
//

//
// Checks if an image object contains an image with transparency
//
function check_transparent( $im ) {
	$width  = imagesx( $im ); // Get the width of the image
	$height = imagesy( $im ); // Get the height of the image

	// We run the image pixel by pixel and as soon as we find a transparent pixel we stop and return true.
	for ( $i = 0; $i < $width; $i++ ) {
		for ( $j = 0; $j < $height; $j++ ) {
			$rgba = imagecolorat( $im, $i, $j );
			if ( ( $rgba & 0x7F000000 ) >> 24 ) {
				return true;
			}
		}
	}

	// If we dont find any pixel the function will return false.
	return false;
}

//
// Merge two images and keep transparency
// the function returns the resulting image ready for saving
//
function imagemergealpha( $flag, $mask, $l, $o, $so ) {
	// create a new image
	$s = imagecreatetruecolor( imagesx( $flag ), imagesy( $flag ) );

	// merge images
	imagealphablending( $s, true );

	imagecopy( $s, $flag, 0, 0, 0, 0, imagesx( $flag ), imagesy( $flag ) );
	imagecopy( $s, $mask, $l, $o, 0, 0, imagesx( $mask ), imagesy( $mask ) );

	// restore the transparency
	imagealphablending( $s, false );

	$w = imagesx( $mask );
	$h = imagesy( $mask );

	for ( $x = 0;$x < $w;$x++ ) {
		for ( $y = 0;$y < $h;$y++ ) {
			$c = imagecolorat( $s, $x, $y );
			$c = imagecolorsforindex( $s, $c );
			$t = 0;

			$ta = @imagecolorat( $flag, $x, $y );
			$ta = imagecolorsforindex( $flag, $ta );
			$t += 127 - $ta['alpha'];

			$ta = @imagecolorat( $mask, $x, $y );
			$ta = imagecolorsforindex( $mask, $ta );
			$t += 127 - $ta['alpha'];

			$t = ( $t > 127 ) ? 127 : $t;
			$t = 127 - $t;
			$c = imagecolorallocatealpha( $s, $c['red'], $c['green'], $c['blue'], $t );
			imagesetpixel( $s, $x, $y, $c );
		}
	}

	imagesavealpha( $s, true );
	return $s;
}


