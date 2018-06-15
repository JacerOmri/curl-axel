<?php

use CurlAxel\RangeUtils;
use PHPUnit\Framework\TestCase;


final class RangeUtilsTest extends TestCase
{

    public function testGetSlices()
    {
        $this->assertEquals([
            [0, 5],
            [6, 10]
        ], RangeUtils::getSlices(10, 2));

        $this->assertEquals([
            [0, 3],
            [4, 7],
            [8, 10]
        ], RangeUtils::getSlices(10, 3));
    }

    public function testGetDashedSlices()
    {
        $this->assertEquals([
            '0-5',
            '6-10'
        ], RangeUtils::getDashedSlices(10, 2));
    }

}