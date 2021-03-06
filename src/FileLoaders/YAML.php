<?php

namespace Aidantwoods\BetterOptions\FileLoaders;

use Aidantwoods\BetterOptions\FileLoader;

use Symfony\Component\Yaml\Yaml as SymfonyYaml;
use Symfony\Component\Yaml\Exception\ParseException;

class YAML implements FileLoader
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
    public static function load(string $file) : array
    {
        try
        {
            $data = SymfonyYaml::parse(file_get_contents($file));
        }
        catch (ParseException $e)
        {
            throw new OptionLoaderException(
                "File $file does not appear to be valid YAML: "
                .$e->getMessage()
            );
        }

        return $data;
    }
}
