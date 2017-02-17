<?php

namespace Aidantwoods\BetterOptions;

use Aidantwoods\BetterOptions\Options\Option;

abstract class OptionParser
{
    private static $argv;

    public static function bindValue(OptionInterface $option)
    {
        # grab argv as early as possible

        if (self::$argv === null)
        {
            self::$argv = $GLOBALS['argv'];
        }

        $type = $option->getType();

        $printableKey = $option->getPrintableName();

        for ($i = 0; $i < count(self::$argv); $i++)
        {
            $arg = self::$argv[$i];

            if (
                preg_match(
                    '/^'
                    .preg_quote($printableKey, '/')
                    .'(?:(?:[=]|\s+)((?:.|\n)*)|$)/',

                    $arg,
                    $matches
                )
            ) {
                # discard value if boolean
                if (Type::normalise($type) === 'boolean')
                {
                    $matches[1] = true;
                }
                # grab value from next option if not in this one
                elseif (empty($matches[1]))
                {
                    if (
                        isset(self::$argv[$i+1])
                        and ! preg_match('/^[-]/', self::$argv[$i+1])
                    ) {
                        $matches[1] = self::$argv[$i+1];
                        $i++;
                    }
                    else
                    {
                        $matches[1] = '';
                    }
                }

                $option->setValue($matches[1]);

                # stop setting values if only one required
                if ( ! Type::hasOuterArray($type) and $type !== 'array')
                {
                    return;
                }
            }
        }
    }
}