<?php

namespace Aidantwoods\BetterOptions;

use Exception;
use stdClass;

use Aidantwoods\BetterOptions\Groups\ORGroup;
use Aidantwoods\BetterOptions\Groups\XORGroup;
use Aidantwoods\BetterOptions\Groups\ANDGroup;
use Aidantwoods\BetterOptions\Options\Option;
use Aidantwoods\BetterOptions\Options\AliasOption;

use Aidantwoods\BetterOptions\Text\HelpFormatter;

class OptionLoader
{
    const GROUP_TYPES = array(
        'OR',
        'AND',
        'XOR'
    );

    const GROUP_NAMESPACE = 'Aidantwoods\\BetterOptions\\Groups\\';

    private $objects = array();

    private $optionCatalogue = array(),
            $groupCatalogue  = array();

     /**
     * Load options from a .json file
     *
     * @param string $config
     */
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

    /**
     * Get an array of all GroupObjects in a hierarchy (groups contain their
     * options)
     *
     * @return GroupObject[]
     */
    public function getObjects() : array
    {
        return $this->objects;
    }

    /**
     * Get an array of all options (no hierarchy, just a list of all options)
     *
     * @return OptionInterface[]
     */
    public function getOptions() : array
    {
        return $this->optionCatalogue;
    }

    /**
     * Get an option by name (make sure to include preceding dashes)
     *
     * @param string $printableName
     *
     * @return ?OptionInterface
     *  returns the option, or null if option does not exist
     */
    public function getOption(string $printableName) : ?OptionInterface
    {
        return $this->optionCatalogue[$printableName] ?? null;
    }

    /**
     * Get an array of all groups that are named
     *
     * @return Group[]
     */
    public function getGroups() : array
    {
        return $this->groupCatalogue;
    }

    /**
     * Get a named group of a particular name
     *
     * @param string $name
     *
     * @return ?Group the group, or null if the group does not exist
     */
    public function getGroup(string $name) : ?Group
    {
        return $this->groupCatalogue[$name] ?? null;
    }

    /**
     * Get an auto-generated help message based on the list of all options,
     * their descriptions, and aliases
     *
     * @return string
     */
    public function getHelp() : string
    {
        return HelpFormatter::generate(
            array_unique($this->optionCatalogue, SORT_REGULAR),
            50
        );
    }

    /**
     * Get an array of response messages from all GroupObjects loaded
     *
     * @return string[]
     */
    public function getResponseMessages() : array
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

    /**
     * Generator function to itterate over an array from a .json file.
     * Individual items are identified and given to the appropriate parser.
     * The resulting object returned by the parser is yielded.
     *
     * @param array $json
     *  an array of items from the entire json file or a part of it
     *
     * @return \Generator|GroupObject[]
     */
    private function itterator(array $json)
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

    /**
     * Identify the object parser that a particular item should be handed to
     *
     * @param stdClass $item a non iterable item from json_decode
     *
     * @return string
     *  the type of parser that should be used to parse the given item
     *
     * @throws Exception if the correc $item parser cannot be identified
     */
    private function identifyObject(stdClass $item)
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

    /**
     * Parse a group from json
     *
     * @param stdClass $item
     *
     * @return Group
     */
    private function parseGroup(stdClass $item) : Group
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

        $name = $item->name ?? null;

        if (isset($name))
        {
            $group->setName($name);

            $this->groupCatalogue[$group->getName()] = $group;
        }

        foreach ($this->itterator($item->members) as $object)
        {
            $group->add($object);
        }

        return $group;
    }

    /**
     * Parse an option from json
     *
     * @param stdClass $item
     *
     * @return Option
     */
    private function parseOption(stdClass $item) : Option
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

        $fixedName = $item->name ?? null;

        $option = new Option($name, $characteristic, $type, $default);

        if (isset($description))
        {
            $option->setDescription($description);
        }

        if (isset($fixedName))
        {
            if ($fixedName[0] === '-')
            {
                throw new Exception(
                    "An option's fixed name may not start with a `-`.\n\n"
                    ." Error near: ".var_export($item, true)
                );
            }

            $option->setFixedName($fixedName);
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

        if (isset($fixedName))
        {
            $this->optionCatalogue[$fixedName] = $option;
        }

        $this->optionCatalogue[$option->getPrintableName()] = $option;

        return $option;
    }

    /**
     * Given a characteristic string, return the associated bitmask
     *
     * @param string $characteristic
     *
     * @return int
     */
    private function optionCharacteristic(string $characteristic) : int
    {
        return Option::CHARACTERISTICS[strtoupper($characteristic)];
    }

    /**
     * Given an arbitrarily nested array of either Response objects or arrays
     * of Response objects, (or Response objects and arrays of arrays of
     * Response objects etc...) yield the response strings
     *
     * @param Response[]|array[] $responses
     *
     * @return \Generator|string[]
     */
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
}
