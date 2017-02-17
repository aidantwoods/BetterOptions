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

    public function get()
    {
        return $this->objects;
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

        $option = new Option($name, $characteristic, $type, $default);

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

        return $option;
    }

    private function optionCharacteristic(string $characteristic)
    {
        return Option::CHARACTERISTICS[strtoupper($characteristic)];
    }
}