<?php

namespace CurlAxel;

class RangeUtils
{
    public static function getSlices($size, $sliceCount)
    {
        $step = $size/$sliceCount;
        $ranges = range(0, $size, $step);
        $ranges[count($ranges) - 1] = $size;
        $slices = [];

        while (count($ranges) > 1) {
            $start = $ranges[0];
            $slices[] = [round($start), round($ranges[1])];
            array_shift($ranges);
            $ranges[0]++;
        }

        return $slices;
    }
    
    public static function getDashedSlices($size, $sliceCount)
    {
        return array_map(function ($e) {
            return implode($e, '-');
        }, self::getSlices($size, $sliceCount));
    }
}
