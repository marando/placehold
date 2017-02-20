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
class Color
{

    public static function hex2HSL($hex)
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

    public static function hex2rgb($hex)
    {
        $hex = str_replace('#', '', $hex);

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

    public static function hsl2Hex($h, $s, $l)
    {
        $h = ($h % 360) / 360;
        $s = $s / 100;
        $l = $l / 100;

        if ($s == 0) {
            $r = $g = $b = $l;
        } else {
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;

            $r = static::hue2rgb($p, $q, $h + 1 / 3);
            $g = static::hue2rgb($p, $q, $h);
            $b = static::hue2rgb($p, $q, $h - 1 / 3);
        }

        return static::rgb2hex($r * 255, $g * 255, $b * 255);
    }

    public static function hue2rgb($p, $q, $t)
    {
        if ($t < 0) {
            $t += 1;
        }
        if ($t > 1) {
            $t -= 1;
        }
        if ($t < 1 / 6) {
            return $p + ($q - $p) * 6 * $t;
        }
        if ($t < 1 / 2) {
            return $q;
        }
        if ($t < 2 / 3) {
            return $p + ($q - $p) * (2 / 3 - $t) * 6;
        }

        return $p;
    }

    public static function rgb2hex($r, $g, $b)
    {
        return
          str_pad(dechex($r), 2, '0', STR_PAD_LEFT) .
          str_pad(dechex($g), 2, '0', STR_PAD_LEFT) .
          str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
    }


}