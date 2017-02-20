<?php

namespace Aidantwoods\BetterOptions;

interface FileLoader
{
    /**
     * Load the given file as an associative array. The array MUST be
     * identical in structure to as returned by PHPs native
     * json_decode($file, true).
     *
     * If a parse error is encountered the FileLoader MUST throw an
     * OptionLoaderException
     *
     * @param string $file the file to load
     *
     * @return array
     *
     * @throws OptionLoaderException
     */
    public static function load(string $file) : array;
}