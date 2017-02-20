<?php

namespace Aidantwoods\BetterOptions;

abstract class Math
{
    /**
     * Perform a real mod (return a positive value), read $a mod $b
     *
     * @param int $a
     * @param int $b
     *
     * @return int
     */
    public static function mod(int $a, int $b) : int
    {
        return ($a % $b) + ($a < 0 ? $b : 0);
    }
}