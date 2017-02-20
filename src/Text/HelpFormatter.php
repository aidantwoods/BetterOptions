<?php

namespace Aidantwoods\BetterOptions\Text;

use Aidantwoods\BetterOptions\Math;

class HelpFormatter
{
    /**
     * Generate help text given an array of options
     *
     * @param Option[] $options
     *
     * @return string;
     */
    public static function generate(
        array $options,
        int $descriptionLength
    ) : string
    {
        $lines = array();
        $nameCols = array();

        $maxLineLength = 0;

        # make sure keys are ordered
        $options = array_values($options);

        foreach ($options as $option)
        {
            $line = '  ';

            $line .= $option->getPrintableName();

            $aliases = $option->getAliasNames();

            if ( ! empty($aliases))
            {
                $line .= ', '.implode(', ', $aliases);
            }

            if (($len = strlen($line)) > $maxLineLength)
            {
                $maxLineLength = $len;
            }

            $nameCols[] = $line;
        }

        $desciptionCharStart = $maxLineLength + 4;

        $desciptionCharStart += Math::mod(4 - $desciptionCharStart, 4);

        foreach ($options as $key => $option)
        {
            $line = $nameCols[$key];

            $description = $option->getDescription();

            if (isset($description))
            {
                self::formatLineWithDescription(
                    $line,
                    $description,
                    $desciptionCharStart,
                    $descriptionLength,
                    $lines
                );
            }
            else
            {
                $lines[] = $line;
            }
        }

        $lines[] = '';

        return implode("\n", $lines);
    }

    /**
     * Format lines that have a description
     *
     * @param string $line
     *  the line to format
     * @param string $description
     *  the description text
     * @param int $desciptionCharStart
     *  the number of chars past a newline that the description column starts
     * @param int $descriptionLength
     *  the hard-wrap length of the description
     * @param array &$lines
     *  a reference to the collection of all lines
     */
    private static function formatLineWithDescription(
        string $line,
        string $description,
        int $desciptionCharStart,
        int $descriptionLength,
        array &$lines
    ) {
        $description = preg_replace('/\n|\r/', '', $description);

        $spaceRepeat = $desciptionCharStart - strlen($line);

        $line .= str_repeat(' ', $spaceRepeat);

        $i = 0;

        foreach (
            self::descriptionTrimmer(
                $description,
                $desciptionCharStart,
                $descriptionLength
            )

            as $i => $piece
        ) {
            $lines[] = ($i === 0 ? $line : '').$piece;
        }

        if ($i > 0)
        {
            $lines[] = '';
        }
    }

    /**
     * Load options from a .json file
     *
     * @param string $description
     *  the description to trim
     * @param string $desciptionCharStart
     *  the number of chars past a newline that the description column starts
     * @param int $descriptionLength
     *  the hard-wrap length of the description
     *
     * @return \Generator|string[]
     *  yields a single line until the given description text is exhausted
     */
    private static function descriptionTrimmer(
        string $description,
        int $desciptionCharStart,
        int $descriptionLength
    ) {
        $remainder = $description;

        $firstLine = true;

        while (strlen($remainder) > 0)
        {
            $space = '';

            if ( ! $firstLine)
            {
                $space = str_repeat(' ', $desciptionCharStart);
            }

            if (($len = strlen($remainder)) > $descriptionLength)
            {
                if (
                    ($n = strrpos(
                        $remainder,
                        ' ',
                        $descriptionLength - $len
                    )) !== false
                ) {
                    $piece = substr($remainder, 0, $n);

                    # plus one to remove leading space
                    $remainder = substr($remainder, $n + 1);
                }
                else
                {
                    $piece = substr($remainder, 0, $descriptionLength);

                    $remainder = substr($remainder, $descriptionLength);
                }
            }
            else
            {
                $piece = $remainder;

                $remainder = '';
            }

            yield $space.$piece;

            $firstLine = false;
        }
    }
}