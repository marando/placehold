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
 * Generates a placeholder image as a base 64 HTML image uri.
 *
 * @package Marando\Placehold
 *
 * @property int    $width   Width of the image.
 * @property int    $height  Height of the image.
 * @property Color  $bgColor Background color of the image.
 * @property Color  $fgColor Foreground (text) color of the image.
 * @property string $font    Font face for the image.
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

    private $format;
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
        // Set defaults...
        $this->format  = static::validateFormat($format);
        $this->width   = static::DefaultWidth;
        $this->height  = static::DefaultHeight;
        $this->bgColor = Color::hex(static::DefaultBackground);
        $this->fgColor = Color::hex(static::DefaultForeground);
        $this->font    = static::DefaultFont;
        $this->padding = 1 - static::DefaultPadding;
    }

    // // // Static

    /**
     * Creates a new placeholder image using the PNG format.
     *
     * @param int $compression Must be 0 to 9, (9 = most compression).
     *
     * @return static
     * @throws \Exception
     */
    public static function png($compression = 9)
    {
        if ($compression > 9 || $compression < 0) {
            throw new \Exception('PNG compression must be 0 to 9');
        }

        $png          = new static('png');
        $png->quality = $compression;

        return $png;
    }

    /**
     * Creates a new placeholder image using the JPEG format.
     *
     * @param int $quality Must be 0 to 100, (0 = most compressed).
     *
     * @return static
     * @throws \Exception
     */
    public static function jpg($quality = 80)
    {
        if ($quality > 100 || $quality < 0) {
            throw new \Exception('JPEG compression must be 0 to 100');
        }

        $jpeg          = new static('jpeg');
        $jpeg->quality = $quality;

        return $jpeg;
    }

    /**
     * Creates a new placeholder image using the GIF format.
     *
     * @return static
     */
    public static function gif()
    {
        return new static('gif');
    }

    //--------------------------------------------------------------------------
    // Builder Functions
    //--------------------------------------------------------------------------

    /**
     * Sets the width and height of this image.
     *
     * @param $width
     * @param $height
     *
     * @return $this
     */
    public function size($width, $height)
    {
        $this->width  = $width;
        $this->height = $height;

        return $this;
    }

    /**
     * Use a random width and height for this image.
     *
     * @param int $min Minimum width/height length.
     * @param int $max Maximum width/height length.
     *
     * @return $this
     */
    public function sizeRand($min = 315, $max = 560)
    {
        if ($max < $min) {
            $temp = $max;
            $max  = $min;
            $min  = $temp;
        }


        $this->size(mt_rand($min, $max), mt_rand($min, $max));

        return $this;
    }

    /**
     * Sets the background color of this image. Valid values are hex, and html
     * hsl and rgb values.
     *
     * @param string|null $color Hex, hsl or rgb.
     *
     * @return $this
     */
    public function bg($color = null)
    {
        // Convert color to Color object and find auto foreground color.
        $this->bgColor = static::getColorInstance($color);
        $this->fgColor = static::autoContrast($this->bgColor);

        return $this;
    }

    /**
     * Uses a random background color for this image. If no arguments are
     * provided a totally random color is selected. If both arguments are
     * provided then a color between the two is randomly selected.
     *
     * @param string|null $colorA
     * @param string|null $colorB
     *
     * @return Placehold
     */
    public function bgRand($colorA = null, $colorB = null)
    {
        if ($colorA && $colorB) {
            $bgColor = static::randColorBetween($colorA, $colorB);
        } else {
            $bgColor = Color::rand();
        }

        return $this->bg($bgColor);
    }

    /**
     * Sets the foreground color of this image. Valid values are hex, and html
     * hsl and rgb values.
     *
     * @param string|null $color Hex, hsl or rgb.
     *
     * @return $this
     */
    public function fg($color)
    {
        // Convert color to Color object and find auto background color.
        $this->fgColor = static::getColorInstance($color);
        $this->bgColor = static::autoContrast($this->fgColor);

        return $this;
    }

    /**
     * Uses a random foreground color for this image. If no arguments are
     * provided a totally random color is selected. If both arguments are
     * provided then a color between the two is randomly selected.
     *
     * @param string|null $colorA
     * @param string|null $colorB
     *
     * @return Placehold
     */
    public function fgRand($colorA = null, $colorB = null)
    {
        if ($colorA && $colorB) {
            $fgColor = static::randColorBetween($colorA, $colorB);
        } else {
            $fgColor = Color::rand();
        }

        return $this->fg($fgColor);
    }

    /**
     * Sets the padding between the width of the image and it's text.
     *
     * @param $padding Total ratio of left and right padding of text.
     *
     * @return $this
     */
    public function padding($padding)
    {
        // Use reciprocal... basically becomes ratio of text size.
        $this->padding = 1 - $padding;

        return $this;
    }

    /**
     * Sets the text of this image. The parameter can either be a string of the
     * text, or a call back which returns the text. The callback has one
     * parameter with this instance set to it for access inside the callback.
     *
     * @param string|callable $text
     *
     * @return $this
     */
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
     * Sets the font face for this image.
     *
     * @param $font Name of the font with weight, e.g. "Raleway 900"
     *
     * @return $this
     */
    public function font($font)
    {
        $this->font = $font;

        return $this;
    }

    /**
     * Generates a random image. Randomizes the size and background.
     *
     * @return Placehold
     */
    public function rand()
    {
        return $this->bgRand()->sizeRand();
    }

    //--------------------------------------------------------------------------
    // Functions
    //--------------------------------------------------------------------------

    /**
     * Renders this image.
     *
     * @return string
     */
    private function render()
    {
        // Create image
        $image = imagecreatetruecolor($this->width, $this->height);

        // Convert colors to GD colors...
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

        // Fill background.
        imagefill($image, 0, 0, $bgColor);

        // Get and prepare image text.
        $text = $this->text ?? "{$this->width}&times;{$this->height}";
        $text = html_entity_decode($text);

        // Write the image text.
        static::imageTtfTextCenter(
          $image,
          $text,
          static::getFontPath($this->font),
          $this->padding,
          $fgColor
        );

        // Return base 64...
        return static::imageToHtmlBase64($image, $this->format, $this->quality);
    }

    //--------------------------------------------------------------------------
    // Static Functions
    //--------------------------------------------------------------------------

    /**
     * Draws centered text with a TTF font on a GD image resource at the
     * specified ratio to the image width.
     *
     * @param $image     GD image resource.
     * @param $text      Text to draw.
     * @param $fontPath  Path to TTF font.
     * @param $fontRatio Ratio of text size to image width.
     * @param $color     Text color.
     */
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

    /**
     * Selects the best contrast color for an input color from choices of white
     * or black.
     *
     * @param Color $c
     *
     * @return Color
     */
    private static function autoContrast(Color $c)
    {
        $black = Color::hex('#000000');
        $white = Color::hex('#ffffff');

        // Use the option with the largest delta...
        $deltaBlack = $c->dist($black);
        $deltaWhite = $c->dist($white);

        if (min($deltaBlack, $deltaWhite) == $deltaBlack) {
            return $white;
        } else {
            return $black;
        }

    }

    /**
     * Gets the path to a font stored locally.
     *
     * @param $font
     *
     * @return string
     */
    private static function getFontPath($font)
    {
        $fontBase = __DIR__ . '/../../../fonts';
        $font     = str_replace(' ', '-', $font);

        // Only return if it exists...
        if ($path = realpath("{$fontBase}/{$font}.ttf")) {
            return $path;
        } else {
            // Return default font
            return static::getFontPath(static::DefaultFont);
        }
    }

    /**
     * Converts a string representation of a color (hex, html hsl or rgb) or a
     * Color instance to a Color instance.
     *
     * @param string|Color $color
     *
     * @return Color
     */
    private static function getColorInstance($color)
    {
        if ($color instanceof Color) {
            return $color;
        }

        return Color::parse($color);
    }

    /**
     * Validates an image format is supported. Throws an exception if it is not
     * otherwise returns the format string.
     *
     * @param string $format
     *
     * @return mixed
     * @throws \Exception
     */
    private static function validateFormat($format)
    {
        $formats = ['png', 'jpeg', 'gif'];

        if (in_array(strtolower($format), $formats)) {
            return $format;
        } else {
            throw new \Exception('invalid format');
        }
    }

    /**
     * Converts a GD image resource to HTML base 64 for embedding in an image
     * tag.
     *
     * @param $image   GD image resource.
     * @param $format  Image format to write.
     * @param $quality Image quality to write (only for JPEG and PNG).
     *
     * @return string
     */
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
     * Picks a random color between two colors.
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

    /**
     * String value is the base 64 representation of the image.
     *
     * @return string
     */
    function __toString()
    {
        return $this->render();
    }

}