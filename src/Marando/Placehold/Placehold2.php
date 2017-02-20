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
class Placeholdss
{
    //--------------------------------------------------------------------------
    // Constants
    //--------------------------------------------------------------------------

    //--------------------------------------------------------------------------
    // Variables
    //--------------------------------------------------------------------------

    private $format;
    private $width = 320;
    private $height = 240;
    private $bgColor;
    private $fgColor;
    private $text;
    private $fontPath;
    private $fontRatio = 0.618;

    //--------------------------------------------------------------------------
    // Constructors
    //--------------------------------------------------------------------------

    private function __construct($format)
    {
        $this->format   = $format;
        $this->fontPath = static::getFontPath('Roboto Condensed Regular');
    }

    // // // Static

    public static function make($format = 'png')
    {
        return new static($format);
    }

    public static function jpeg()
    {
        return static::make('jpeg');
    }

    public static function png()
    {
        return static::make('png');
    }

    public static function gif()
    {
        return static::make('gif');
    }

    public static function svg()
    {
        return static::make('svg');
    }

    //--------------------------------------------------------------------------
    // Builder Functions
    //--------------------------------------------------------------------------

    public function size($width, $height)
    {
        $this->width  = $width;
        $this->height = $height;

        return $this;
    }

    public function bgHex($hex)
    {
        $this->bgColor = $hex;

        if ($this->fgColor == null) {
            $this->fgColor = static::blackOrWhite($hex);
        }

        return $this;
    }

    public function bgHSL($h, $s, $l)
    {
        $this->bgHex(Color::hsl2Hex($h, $s, $l));

        return $this;
    }

    public function bgRGB($r, $g, $b)
    {
        $this->bgHex(Color::rgb2hex($r, $g, $b));

        return $this;
    }

    public function fgHex($hex)
    {
        $this->fgColor = $hex;

        return $this;
    }

    public function fgHSL($h, $s, $l)
    {
        $this->fgHex(Color::hsl2Hex($h, $s, $l));

        return $this;
    }

    public function fgRGB($hsl)
    {
        return $this;
    }

    public function randSize($min = 500, $max = 900)
    {
        return $this->size(rand($min, $max), rand($min, $max));
    }

    public function randBg($h = [0, 360], $s = [0, 100], $l = [0, 100])
    {
        $h = rand($h[0], $h[1]);
        $s = rand($s[0], $s[1]);
        $l = rand($l[0], $l[1]);

        $this->bgHSL($h, $s, $l);

        if ($this->fgColor == null) {
            $contrast = static::blackOrWhite($this->bgColor);
            $this->fgHex($contrast);
        }

        return $this;
    }

    public function randFg($h = [0, 360], $s = [0, 100], $l = [0, 100])
    {
        $h = rand($h[0], $h[1]);
        $s = rand($s[0], $s[1]);
        $l = rand($l[0], $l[1]);

        return $this->fgHSL($h, $s, $l);
    }

    public function fontRatio($ratio)
    {
        $this->fontRatio = $ratio;

        return $this;
    }

    public function text($text)
    {
        $this->text = $text;

        return $this;
    }

    //--------------------------------------------------------------------------
    // Private Functions
    //--------------------------------------------------------------------------

    private function render()
    {
        // Create image
        $image = imagecreatetruecolor($this->width, $this->height);

        // Get colors
        $bgColor = static::hex2GDcolor($image, $this->bgColor);
        $fgColor = static::hex2GDcolor($image, $this->fgColor);

        // Fill background
        imagefill($image, 0, 0, $bgColor);

        // Get and prepare image text
        $text = $this->text ?? "{$this->width}&times;{$this->height}";
        $text = html_entity_decode($text);

        static::imageTtfTextCenter(
          $image,
          $text,
          $this->fontPath,
          $this->fontRatio,
          $fgColor
        );

        return static::imageToHtmlBase64($image, $this->format);
    }



    //--------------------------------------------------------------------------
    // Static Functions
    //--------------------------------------------------------------------------

    private static function imageTtfTextCenter(
      $image,
      $text,
      $fontPath,
      $fontRatio,
      $color
    ) {
        $width  = imagesx($image);
        $height = imagesy($image);

        // Calculate font size
        $fontSize    = 1;
        $txtMaxWidth = intval($fontRatio * $width);
        do {
            $fontSize++;
            $p        = imagettfbbox($fontSize, 0, $fontPath, $text);
            $txtWidth = $p[2] - $p[0];
        } while ($txtWidth <= $txtMaxWidth);

        // Center text
        $y = $height * 0.5 + ($p[1] - $p[7]) / 2;
        $x = ($width - $txtWidth) / 2;

        imagettftext(
          $image, $fontSize, 0, $x, $y, $color, $fontPath, $text);
    }

    private static function blackOrWhite($hex)
    {
        list($hue, $sat, $lum) = Color::hex2HSL($hex);

        return $lum > 0.5 ? '#000000' : '#ffffff';
    }

    private static function randHexColor()
    {
        return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }

    private static function hex2GDcolor($image, $hex)
    {
        list($r, $g, $b) = Color::hex2rgb($hex);

        return imagecolorallocate($image, $r, $g, $b);
    }

    private static function getFontPath($font)
    {
        $fontBase = __DIR__ . '/../../../fonts';
        $font     = str_replace(' ', '-', $font);

        return realpath("{$fontBase}/{$font}.ttf");
    }

    private static function imageToHtmlBase64($image, $format)
    {
        ob_start();

        switch ($format) {
            case 'jpeg':
                imagejpeg($image);
                break;

            case 'gif':
                imagegif($image);
                break;

            default:
            case 'png':
                imagepng($image);
                break;
        }


        $binary = ob_get_contents();
        ob_end_clean();

        return "data:image/{$format};base64," . base64_encode($binary);
    }

    //--------------------------------------------------------------------------
    // Overrides
    //--------------------------------------------------------------------------

    function __toString()
    {
        return $this->render();
    }

}