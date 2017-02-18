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
 * Generates base64 placeholder holder image data.
 *
 * @package Marando\Placehold
 */
class Placehold
{

    private $height;
    private $width;
    private $fgColor;
    private $bgColor;
    private $text;
    private $font;
    private $padding;

    /**
     * Creates a new placeholder image.
     *
     * @param int    $width   Image width
     * @param int    $height  Image height
     * @param string $bgColor Hex background color
     * @param string $fgColor Hex foreground color
     * @param null   $text    Optional alternate text
     * @param float  $padding
     * @param string $font    Optional alternate font
     */
    public function __construct(
      $width = 250,
      $height = 250,
      $bgColor = '#201d1d',
      $fgColor = '#b3b2b2',
      $text = null,
      $padding = 0.618,
      $font = 'Raleway-Bold'
    ) {
        $this->width   = $width;
        $this->height  = $height;
        $this->bgColor = $bgColor;
        $this->fgColor = $fgColor;
        $this->text    = $text;
        $this->font    = $font;
        $this->padding = $padding;
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
        $image   = imagecreatetruecolor($this->width, $this->height);
        $bgColor = $this->hexToResourceColor($image, $this->bgColor);
        $fgColor = $this->hexToResourceColor($image, $this->fgColor);

        // Color the background
        imagefill($image, 0, 0, $bgColor);

        // Calculate font size
        $fontSize    = 1;
        $txtMaxWidth = intval($this->padding * $this->width);
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
        imagepng($img);
        $binary = ob_get_contents();
        ob_end_clean();

        return "data:image/png;base64," . base64_encode($binary);
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
