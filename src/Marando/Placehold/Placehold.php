<?php

/*
 * Copyright (C) 2017 Ashley Marando
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace Marando\Placehold;

/**
 * Generates a placeholder image as a base 64 HTML image uri.
 *
 * @package Marando\Placehold
 */
class Placehold
{
    //--------------------------------------------------------------------------
    // Constants
    //--------------------------------------------------------------------------

    /**
     * Default font for the placeholder image.
     */
    const DefaultFont = 'Roboto Condensed Regular';

    /**
     * Threshold of background brightness for using black as auto foreground.
     */
    const BlackThreshold = 0.5;

    //--------------------------------------------------------------------------
    // Variables
    //--------------------------------------------------------------------------

    private $height = 250;
    private $width = 250;
    private $fgColor = 'auto';
    private $bgColor = '#444';
    private $text = null;
    private $font = null;
    private $ratio = 0.618;
    private $format = 'png';

    //--------------------------------------------------------------------------
    // Constructors
    //--------------------------------------------------------------------------

    /**
     * Creates a new placeholder image.
     *
     * @param $format
     */
    private function __construct($format)
    {
        $this->format = $format;
    }

    /**
     * Make a new placeholder image.
     *
     * @param string $format
     *
     * @return static
     */
    public static function make($format = 'png')
    {
        return new static($format);
    }

    /**
     * Make a new random placeholder image. This randomizes both the dimensions
     * and the background color. Foreground will be automatically chosen based
     * on the lightness of the background.
     *
     * @param int $min Minimum dimensions.
     * @param int $max Maximum dimensions.
     *
     * @return $this
     */
    public static function rand($min = 500, $max = 900)
    {
        $width  = rand($min, $max);
        $height = rand($min, $max);

        return static::make()->size($width, $height)->bg("rand");
    }

    //--------------------------------------------------------------------------
    // Functions
    //--------------------------------------------------------------------------

    /**
     * Sets the width and height of the image.
     *
     * @param int $width
     * @param int $height
     *
     * @return $this
     */
    public function size(int $width, int $height)
    {
        $this->width  = $width;
        $this->height = $height;

        return $this;
    }

    /**
     * Sets the foreground color.
     *
     * Note: Default behavior is to chose either black or white based on the
     * brightness of the background color.
     *
     * Passing 'inv' will cause the foreground color to be the inverse of the
     * background color.
     *
     * @param $hex
     *
     * @return $this
     */
    public function fg($hex = 'auto')
    {
        if ($hex == 'rand') {
            $this->fgColor = $this->randHex();
        } else {
            $this->fgColor = $hex;
        }

        return $this;
    }

    /**
     * Sets the background color.
     *
     * @param $hex
     *
     * @return $this
     */
    public function bg($hex)
    {
        if ($hex == 'rand') {
            $this->bgColor = $this->randHex();
        } else {
            $this->bgColor = $hex;
        }

        return $this;
    }

    /**
     * Sets the font typeface.
     *
     * @param $font
     *
     * @return $this
     */
    public function font($font)
    {
        $this->font = $font;

        return $this;
    }

    /**
     * Sets the text of the image. Default is the dimensions of the image.
     *
     * @param $text
     *
     * @return $this
     */
    public function text($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Sets the maximum font size as a ratio of the image width.
     *
     * @param $ratio
     *
     * @return $this
     */
    public function maxFont($ratio)
    {
        $this->ratio = $ratio;

        return $this;
    }

    // // // Private

    /**
     * Renders this instance to a base 64 string.
     *
     * @return string
     */
    private function render()
    {
        // Create image and colors.
        $image = imagecreatetruecolor($this->width, $this->height);

        // Set the foreground color...
        if ($this->fgColor == 'auto') {
            // If auto foreground, find appropriate color based on background.
            $this->fgColor = static::autoContrastColor($this->bgColor);
        } elseif ($this->fgColor == 'inv') {
            // If inverse, find the inverse of the background.
            $this->fgColor = $this->inverseHex($this->bgColor);
        }

        // Create colors...
        $bgColor = $this->hexToResourceColor($image, $this->bgColor);
        $fgColor = $this->hexToResourceColor($image, $this->fgColor);

        // Color the background
        imagefill($image, 0, 0, $bgColor);

        // Calculate font size
        $text        = $this->getText();
        $fontPath    = $this->getFontPath($this->font);
        $fontSize    = 1;
        $txtMaxWidth = intval($this->ratio * $this->width);
        do {
            $fontSize++;
            $p        = imagettfbbox($fontSize, 0, $fontPath, $text);
            $txtWidth = $p[2] - $p[0];
        } while ($txtWidth <= $txtMaxWidth);

        // Center text
        $y = $this->height * 0.5 + ($p[1] - $p[7]) / 2;
        $x = ($this->width - $txtWidth) / 2;

        // Draw text
        imagettftext($image, $fontSize, 0, $x, $y, $fgColor, $fontPath, $text);

        // Return base 64
        return $this->imageToBase64($image, $this->format);
    }

    /**
     * Gets the text for tis instance.
     *
     * @return null|string
     */
    private function getText()
    {
        $x = html_entity_decode("&times;");

        return $this->text ? $this->text : "{$this->width}{$x}{$this->height}";
    }

    // // // Static

    /**
     * Gets the path to a font, returns the default if the font does not exist.
     *
     * @return string
     */
    private static function getFontPath($font)
    {
        $font     = str_replace(' ', '-', $font);
        $fontPath = realpath(__DIR__ . "/../../../fonts/{$font}.ttf");

        if (file_exists($fontPath)) {
            return $fontPath;
        } else {
            $default = str_replace(' ', '-', static::DefaultFont);

            return realpath(__DIR__ . "/../../../fonts/{$default}.ttf");
        }

    }

    private static function hex2Rgb($hex)
    {
        $hex = preg_replace("/[^a-fA-F0-9]+/", "", $hex);

        if (strlen($hex) == 3) {
            $r = hexdec($hex[0] . $hex[0]);
            $g = hexdec($hex[1] . $hex[1]);
            $b = hexdec($hex[2] . $hex[2]);
        } else {
            $r = hexdec($hex[0] . $hex[1]);
            $g = hexdec($hex[2] . $hex[3]);
            $b = hexdec($hex[4] . $hex[5]);
        }

        return [$r, $g, $b];
    }

    /**
     * Converts a hex color to a GD color.
     *
     * @param $img Image resource color will be used in.
     * @param $hex Hex of color.
     *
     * @return int
     */
    private static function hexToResourceColor($img, $hex)
    {
        $hex = preg_replace("/[^a-fA-F0-9]+/", "", $hex);

        list($r, $g, $b) = static::hex2Rgb($hex);

        return imagecolorallocate($img, $r, $g, $b);
    }

    /**
     * Converts an image resource to a base64 string.
     *
     * @param $img
     *
     * @return string
     */
    private static function imageToBase64($img, $format)
    {
        ob_start();

        switch ($format) {
            case 'jpeg':
                imagejpeg($img);
                break;

            case 'gif':
                imagegif($img);
                break;

            default:
            case 'png':
                imagepng($img);
                break;
        }


        $binary = ob_get_contents();
        ob_end_clean();

        return "data:image/{$format};base64," . base64_encode($binary);
    }

    /**
     * Generates a random hex color.
     *
     * @return string
     */
    private static function randHex()
    {
        return sprintf('%06X', mt_rand(0, 0xFFFFFF));
    }

    /**
     * Automatically returns white or black based on best contrast to input hex
     * color.
     *
     * @param $hex
     *
     * @return string
     */
    private static function autoContrastColor($hex)
    {
        list($r, $g, $b) = static::hex2Rgb($hex);
        list($H, $S, $L) = static::hex2Hsl($hex);

        return $L > static::BlackThreshold ? '#000' : '#fff';
    }

    /**
     * Inverts a hex color.
     *
     * @param $hex
     *
     * @return string
     */
    private static function inverseHex($hex)
    {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) != 6) {
            return '000000';
        }
        $rgb = '';
        for ($x = 0; $x < 3; $x++) {
            $c = 255 - hexdec(substr($hex, (2 * $x), 2));
            $c = ($c < 0) ? 0 : dechex($c);
            $rgb .= (strlen($c) < 2) ? '0' . $c : $c;
        }

        return '#' . $rgb;
    }

    /**
     * Converts a hex color to HSL
     *
     * @param $hex
     *
     * @return array
     */
    private static function hex2Hsl($hex)
    {
        $hex = str_replace('#', '', $hex);

        if (strlen($hex) == 3) {
            $hex =
              $hex[0] . $hex[0] .
              $hex[1] . $hex[1] .
              $hex[2] . $hex[2];
        }

        $hex = [$hex[0] . $hex[1], $hex[2] . $hex[3], $hex[4] . $hex[5]];
        $rgb = array_map(function ($part) {
            return hexdec($part) / 255;
        }, $hex);

        $max = max($rgb);
        $min = min($rgb);

        $l = ($max + $min) / 2;

        if ($max == $min) {
            $h = $s = 0;
        } else {
            $diff = $max - $min;
            $s    = $l > 0.5 ? $diff / (2 - $max - $min) : $diff / ($max + $min);

            switch ($max) {
                case $rgb[0]:
                    $h = ($rgb[1] - $rgb[2]) / $diff + ($rgb[1] < $rgb[2] ? 6 : 0);
                    break;
                case $rgb[1]:
                    $h = ($rgb[2] - $rgb[0]) / $diff + 2;
                    break;
                case $rgb[2]:
                    $h = ($rgb[0] - $rgb[1]) / $diff + 4;
                    break;
            }

            $h /= 6;
        }

        return [$h, $s, $l];
    }

    //--------------------------------------------------------------------------
    // Overloads
    //--------------------------------------------------------------------------

    /**
     * String value of this object.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

}
