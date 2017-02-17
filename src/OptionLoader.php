<?php

namespace Aidantwoods\BetterOptions;

use Exception;

use Aidantwoods\BetterOptions\Groups\ORGroup;
use Aidantwoods\BetterOptions\Groups\XORGroup;
use Aidantwoods\BetterOptions\Groups\ANDGroup;
use Aidantwoods\BetterOptions\Options\Option;
use Aidantwoods\BetterOptions\Options\AliasOption;

class OptionLoader
{
    /**
     * Load options from a .json file
     *
     * @param string $config
     *
     * @return GroupObject
     */

    const GROUP_TYPES = array(
        'OR',
        'AND',
        'XOR'
    );

    const GROUP_NAMESPACE = 'Aidantwoods\\BetterOptions\\Groups\\';

    private $objects = array();

    private $optionCatalogue = array();

    private $lineChars = 50;

    public function __construct(string $config)
    {
        if ( ! is_file($config))
        {
            throw new Exception("File $config not found");
        }

        $json = json_decode(file_get_contents($config));

        if ( ! isset($json))
        {
            throw new Exception(
                "File $config does not appear to be valid JSON"
            );
        }

        foreach ($this->itterator($json) as $object)
        {
            $this->objects[] = $object;
        }
    }

    public function getObjects()
    {
        return $this->objects;
    }

    public function getOptions() : array
    {
        return $this->optionCatalogue;
    }

    public function getOption(string $printableName) : Option
    {
        return $this->optionCatalogue[$printableName] ?? null;
    }

    public function getHelp() : string
    {
        $lines = array();
        $nameCols = array();

        $maxLineLength = 0;

        # make sure keys are ordered
        $options = array_values($this->optionCatalogue);

        foreach ($options as $option)
        {
            $line = '';

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

        $desciptionCharStart += 4 - ($desciptionCharStart % 4);

        foreach ($options as $key => $option)
        {
            $line = $nameCols[$key];

            $description = $option->getDescription();

            if (isset($description))
            {
                $this->formatLineWithDescription(
                    $line,
                    $description,
                    $desciptionCharStart,
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

    public function getResponseMessages()
    {
        $responses = array();

        foreach ($this->objects as $object)
        {
            $responses[] = $object->respond();
        }

        $messages = array();

        foreach ($this->responder($responses) as $message)
        {
            $messages[] = $message;
        }

        return $messages;
    }

    private function itterator($json)
    {
        foreach ($json as $item)
        {
            if (is_array($item))
            {
                $this->itterator($item);
            }

            $object = $this->identifyObject($item);

            yield $this->{"parse$object"}($item);
        }
    }

    private function identifyObject($item)
    {
        if (
            isset($item->group)
            and isset($item->members)
            and is_array($item->members)
            and ! isset($item->option)
        ) {
            return "Group";
        }
        elseif (
            isset($item->option)
            and ! isset($item->members)
            and ! isset($item->group)
        ) {
            return "Option";
        }
        else
        {
            throw new Exception("Error near ".var_export($item, true));
        }
    }

    private function parseGroup($item) : Group
    {
        $type = strtoupper($item->group);

        if ( ! in_array($type, self::GROUP_TYPES))
        {
            throw new Exception(
                "Unrecognised group type $type near ".var_export($item, true)
            );
        }

        $groupClass = self::GROUP_NAMESPACE."${type}Group";

        $group = new $groupClass();

        foreach ($this->itterator($item->members) as $object)
        {
            $group->add($object);
        }

        return $group;
    }

    private function parseOption($item) : Option
    {
        $name = $item->option;

        $characteristic = $item->characteristic ?? null;

        if (isset($item->characteristic))
        {
            $characteristic = $this->optionCharacteristic($characteristic);
        }

        $type = $item->type ?? null;

        $default = $item->default ?? null;

        $description = $item->description ?? null;

        $option = new Option($name, $characteristic, $type, $default);

        if (isset($description))
        {
            $option->setDescription($description);
        }

        if (isset($item->aliases))
        {
            foreach ($item->aliases as $alias)
            {
                $aliasCharacteristic = $alias->characteristic ?? null;

                if (isset($aliasCharacteristic))
                {
                    $aliasCharacteristic = $this->optionCharacteristic(
                        $aliasCharacteristic
                    );
                }

                new AliasOption($alias->option, $aliasCharacteristic, $option);
            }
        }

        $this->optionCatalogue[$option->getPrintableName()] = $option;

        return $option;
    }

    private function optionCharacteristic(string $characteristic) : int
    {
        return Option::CHARACTERISTICS[strtoupper($characteristic)];
    }

    private function responder(array &$responses)
    {
        foreach ($responses as &$response)
        {
            if (is_array($response))
            {
                foreach ($response as $response1)
                {
                    $responses[] = $response1;
                }
            }
            else
            {
                yield $response->getMessage();
            }
        }
    }

    private function descriptionTrimmer(
        string $description,
        int $desciptionCharStart
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

            if (($len = strlen($remainder)) > $this->lineChars)
            {
                if (
                    ($n = strrpos(
                        $remainder,
                        ' ',
                        $this->lineChars - $len
                    )) !== false
                ) {
                    $piece = substr($remainder, 0, $n);

                    # plus one to remove leading space
                    $remainder = substr($remainder, $n + 1);
                }
                else
                {
                    $piece = substr($remainder, 0, $this->lineChars
                    );

                    $remainder = substr($remainder, $this->lineChars);
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

    private function formatLineWithDescription(
        string $line,
        string $description,
        int $desciptionCharStart,
        array &$lines
    ) {
        $description = preg_replace('/\n|\r/', '', $description);

        $spaceRepeat = $desciptionCharStart - strlen($line);

        $line .= str_repeat(' ', $spaceRepeat);

        $i = 0;

        foreach (
            $this->descriptionTrimmer($description, $desciptionCharStart)
            as $i => $piece
        ) {
            $lines[] = ($i === 0 ? $line : '').$piece;
        }

        if ($i > 0)
        {
            $lines[] = '';
        }
    }
}
