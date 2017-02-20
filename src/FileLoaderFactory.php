<?php

namespace Aidantwoods\BetterOptions;

use Exception;

abstract class FileLoaderFactory implements FileLoader
{
    private static $classMap = array(
        'json' => 'Aidantwoods\BetterOptions\FileLoaders\JSON'
    );

    const SYMFONY_YAML = 'Symfony\Component\Yaml\Yaml';
    const YAML_ADAPTER = 'Aidantwoods\BetterOptions\FileLoaders\YAML';

    /**
     * Load $file using the appropriate class based on $type
     * or the extension if $type not defined
     *
     * @param string $file
     * @param string $type
     *
     * @return array
     */
    public static function load(string $file, ?string $type = null) : array
    {
        if (isset($type))
        {
            if (isset(self::$classMap[$type]))
            {
                $class = self::$classMap[$type];

                $data = $class::load($file);
            }
            else
            {
                throw new Exception(
                    "There is no registered handler for the specified type "
                    .$type
                );
            }
        }
        else
        {
            self::registerSymfonyYaml();

            foreach (self::$classMap as $ext => $class)
            {
                if (preg_match('/[.]'.preg_quote($ext, '/').'$/i', $file))
                {
                    $data = $class::load($file);
                    break;
                }
            }
        }

        if ( ! isset($data))
        {
            throw new OptionLoaderException(
                "File $file does not appear to be valid"
            );
        }

        return $data;
    }

    /**
     * Register a custom class to handle a particular file extension
     *
     * @param string $fileExtension
     * @param string $class
     *  The fully qualified class name. The given class MUST implement the
     *  Aidantwoods\BetterOptions\FileLoader interface
     *
     * @throws Exception if the class fails to implement the required interface
     */
    public static function registerExtension(string $fileExtension, $class)
    {
        if (
            in_array(
                'Aidantwoods\BetterOptions\FileLoader',
                class_implements($class),
                true
            )
        ) {
            self::$classMap[strtolower($fileExtension)] = $class;
        }
        else
        {
            throw new Exception(
                "Classes must implement the "
                ."Aidantwoods\BetterOptions\FileLoader interface in order to "
                ."be registered in the FileLoaderFactory. $class does not "
                ."implement this interface."
            );
        }
    }

    /**
     * Register the built in YAML adapter for Symfony's YAML parser if
     * Symfony's YAML parser is loaded and no class is loaded for the YAML
     * extension
     */
    private static function registerSymfonyYaml()
    {
        if (
            ! isset(self::$classMap['yaml'])
            and ! isset(self::$classMap['yml'])
            and class_exists(self::SYMFONY_YAML)
        ) {
            self::registerExtension('yaml', self::YAML_ADAPTER);
            self::registerExtension('yml', self::YAML_ADAPTER);
        }
    }
}