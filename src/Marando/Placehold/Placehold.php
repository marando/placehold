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

use Marando\Color\Color;

/**
 *
 *
 * @package Marando\Placehold
 *
 * @property int    $width
 * @property int    $height
 * @property Color  $bgColor
 * @property Color  $fgColor
 * @property string $font
 */
class Placehold
{
    //--------------------------------------------------------------------------
    // Constants
    //--------------------------------------------------------------------------

    /**
     * Default background color.
     */
    const DefaultBackground = '#201e1e';

    /**
     * Default foreground color.
     */
    const DefaultForeground = '#a09c9c';

    /**
     * Default image width.
     */
    const DefaultWidth = 320;

    /**
     * Default image height.
     */
    const DefaultHeight = 240;

    /**
     * Default font face.
     */
    const DefaultFont = 'Roboto Condensed 300';

    /**
     * Default font width ratio of image width.
     */
    const DefaultPadding = 0.618 ** 2;

    //--------------------------------------------------------------------------
    // Variables
    //--------------------------------------------------------------------------

    private $width;
    private $height;
    private $bgColor;
    private $fgColor;
    private $font;
    private $padding;
    private $text;
    private $quality;

    function __get($name)
    {
        switch ($name) {
            case 'width':
            case 'height':
            case 'bgColor':
            case 'fgColor':
            case 'font':
            case 'padding':
                return $this->{$name};
        }
    }

    //--------------------------------------------------------------------------
    // Constructors
    //--------------------------------------------------------------------------

    /**
     * Private Placehold constructor.
     *
     * @param $format
     */
    private function __construct($format)
    {
        $this->format = static::validateFormat($format);

        $this->width  = static::DefaultWidth;
        $this->height = static::DefaultHeight;

        $this->bgColor = Color::hex(static::DefaultBackground);
        $this->fgColor = Color::hex(static::DefaultForeground);

        $this->font    = static::DefaultFont;
        $this->padding = 1 - static::DefaultPadding;
    }

    // // // Private

    /**
     * @param int $compression Must be 0 (least compression) to 9 (most).
     *
     * @return static
     */
    public static function png($compression = 9)
    {
        $png          = new static('png');
        $png->quality = $compression;

        return $png;
    }

    public static function jpg($quality = 80)
    {
        $jpeg          = new static('jpeg');
        $jpeg->quality = $quality;

        return $jpeg;
    }

    public static function gif()
    {
        return new static('gif');
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

    public function sizeRand($min = 315, $max = 560)
    {
        $this->size(mt_rand($min, $max), mt_rand($min, $max));

        return $this;
    }

    public function bg($color = null)
    {
        $this->bgColor = static::getColorInstance($color);
        $this->fgColor = static::autoContrast($this->bgColor);

        return $this;
    }

    public function bgRand($colorA = null, $colorB = null)
    {
        if ($colorA && $colorB) {
            $bgColor = static::randColorBetween($colorA, $colorB);
        } else {
            $bgColor = Color::rand();
        }

        return $this->bg($bgColor);
    }

    public function fg($color)
    {
        $this->fgColor = static::getColorInstance($color);
        $this->bgColor = static::autoContrast($this->fgColor);

        return $this;
    }

    public function fgRand($colorA = null, $colorB = null)
    {
        if ($colorA && $colorB) {
            $fgColor = static::randColorBetween($colorA, $colorB);
        } else {
            $fgColor = Color::rand();
        }

        return $this->fg($fgColor);
    }

    public function padding($padding)
    {
        $this->padding = 1 - $padding;

        return $this;
    }

    public function text($text)
    {
        if (is_string($text)) {
            $this->text = $text;
        }

        if (is_callable($text)) {
            $this->text = $text($this);
        }

        return $this;
    }

    /**
     * @param $font
     *
     * @return $this
     */
    public function font($font)
    {
        $this->font = $font;

        return $this;
    }

    public function rand()
    {
        return $this->bgRand()->sizeRand();
    }

    //--------------------------------------------------------------------------
    // Functions
    //--------------------------------------------------------------------------

    private function render()
    {
        $image = imagecreatetruecolor($this->width, $this->height);

        $bgColor =
          imagecolorallocate(
            $image,
            $this->bgColor->r,
            $this->bgColor->g,
            $this->bgColor->b
          );

        $fgColor =
          imagecolorallocate(
            $image,
            $this->fgColor->r,
            $this->fgColor->g,
            $this->fgColor->b
          );

        imagefill($image, 0, 0, $bgColor);

        // Get and prepare image text
        $text = $this->text ?? "{$this->width}&times;{$this->height}";
        $text = html_entity_decode($text);

        static::imageTtfTextCenter(
          $image,
          $text,
          static::getFontPath($this->font),
          $this->padding,
          $fgColor
        );

        return static::imageToHtmlBase64($image, $this->format, $this->quality);
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

    private static function autoContrast(Color $c)
    {
        $black = Color::hex('#000000');
        $white = Color::hex('#ffffff');

        $deltaBlack = $c->dist($black);
        $deltaWhite = $c->dist($white);

        if (min($deltaBlack, $deltaWhite) == $deltaBlack) {
            return $white;
        } else {
            return $black;
        }

    }

    private static function getFontPath($font)
    {
        $fontBase = __DIR__ . '/../../../fonts';
        $font     = str_replace(' ', '-', $font);

        if ($path = realpath("{$fontBase}/{$font}.ttf")) {
            return $path;
        }

        return static::getFontPath(static::DefaultFont);
    }

    private static function getColorInstance($color)
    {
        if ($color instanceof Color) {
            return $color;
        }

        return Color::parse($color);
    }

    private static function validateFormat($format)
    {
        $formats = ['png', 'jpeg', 'gif'];

        if (in_array(strtolower($format), $formats)) {
            return $format;
        } else {
            throw new \Exception('invalid format');
        }
    }

    private static function imageToHtmlBase64($image, $format, $quality)
    {
        ob_start();

        switch ($format) {
            case 'jpeg':
                imagejpeg($image, null, $quality);
                break;

            case 'gif':
                imagegif($image);
                break;

            default:
            case 'png':
                imagepng($image, null, $quality);
                break;
        }


        $binary = ob_get_contents();
        ob_end_clean();

        return "data:image/{$format};base64," . base64_encode($binary);
    }

    /**
     * Randomizes a color between two colors.
     *
     * @param $colorA
     * @param $colorB
     *
     * @return Color
     */
    private static function randColorBetween($colorA, $colorB)
    {
        $colorA = static::getColorInstance($colorA);
        $colorB = static::getColorInstance($colorB);

        // min/max hue
        $h0 = min($colorA->h, $colorB->h);
        $h1 = max($colorA->h, $colorB->h);

        // min/max sat
        $s0 = min($colorA->s, $colorB->s);
        $s1 = max($colorA->s, $colorB->s);

        // min/max lum
        $l0 = min($colorA->l, $colorB->l);
        $l1 = max($colorA->l, $colorB->l);

        // Random color with above params...
        return Color::rand([$h0, $h1], [$s0, $s1], [$l0, $l1]);
    }

    //--------------------------------------------------------------------------
    // Overrides
    //--------------------------------------------------------------------------

    function __toString()
    {
        return $this->render();
    }

}