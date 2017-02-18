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
class Placehold2
{
    private $height = 250;
    private $width = 250;
    private $fgColor = 'auto';
    private $bgColor = '#444';
    private $text = null;
    private $font = 'Raleway-Regular';
    private $ratio = 0.618;
    private $format = 'png';

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

        return Placehold2::make()->size($width, $height)->bg("rand");
    }

    // // // 

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
            $this->fgColor = $this->randomHex();
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
            $this->bgColor = $this->randomHex();
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
        $this->font = str_replace(' ', '-', $font);

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

    // // //

    /**
     * Generates a random hex color.
     *
     * @return string
     */
    private function randomHex()
    {
        return sprintf('%06X', mt_rand(0, 0xFFFFFF));
    }

    /**
     * Renders this instance to a base 64 string.
     *
     * @return string
     */
    private function render()
    {
        // Image text
        $text = $this->getText();

        // Create image and colors.
        $image = imagecreatetruecolor($this->width, $this->height);

        // If auto foreground, find appropriate color based on background...
        if ($this->fgColor == 'auto') {
            $r = hexdec(substr($this->bgColor, 0, 2));
            $g = hexdec(substr($this->bgColor, 2, 2));
            $b = hexdec(substr($this->bgColor, 4, 2));

            // Get average brightness, and threshold to using white
            $avgBri      = ($r + $g + $b) / 3;
            $whiteThresh = 40;

            $this->fgColor = $avgBri > $whiteThresh * 2.55 ? '#000' : '#fff';
        }

        if ($this->fgColor == 'inv') {
            $this->fgColor = $this->hexInverse($this->bgColor);
        }

        // Create colors...
        $bgColor = $this->hexToResourceColor($image, $this->bgColor);
        $fgColor = $this->hexToResourceColor($image, $this->fgColor);

        // Color the background
        imagefill($image, 0, 0, $bgColor);

        // Calculate font size
        $fontSize    = 1;
        $txtMaxWidth = intval($this->ratio * $this->width);
        $fontPath    = realpath(__DIR__ . "/../../../fonts/{$this->font}.ttf");
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

        return $this->imageToBase64($image);
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

    /**
     * Converts a hex color to a GD color.
     *
     * @param $img Image resource color will be used in.
     * @param $hex Hex of color.
     *
     * @return int
     */
    private function hexToResourceColor($img, $hex)
    {
        $hex = preg_replace("/[^a-fA-F0-9]+/", "", $hex);

        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }

        return imagecolorallocate($img, $r, $g, $b);
    }

    /**
     * Converts an image resource to a base64 string.
     *
     * @param $img
     *
     * @return string
     */
    private function imageToBase64($img)
    {
        ob_start();

        switch ($this->format) {
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

        return "data:image/{$this->format};base64," . base64_encode($binary);
    }

    private function hexInverse($hex)
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
     * String value of this object.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

}
