<?php

namespace Aidantwoods\BetterOptions;

use Aidantwoods\BetterOptions\Groups\ORGroup;
use Aidantwoods\BetterOptions\Groups\XORGroup;
use Aidantwoods\BetterOptions\Groups\ANDGroup;
use Aidantwoods\BetterOptions\Options\Option;
use Aidantwoods\BetterOptions\Options\AliasOption;

use Aidantwoods\BetterOptions\Text\HelpFormatter;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class OptionLoader
{
    const GROUP_TYPES = array(
        'OR',
        'AND',
        'XOR'
    );

    const GROUP_NAMESPACE = 'Aidantwoods\\BetterOptions\\Groups\\';

    const JSON = 0b001;
    const YAML = 0b010;
    const AUTO = 0b100;

    const EXT_TYPE_MAP = array(
        'json' => self::JSON,
        'yaml' => self::YAML
    );

    private $objects = array();

    private $optionCatalogue = array(),
            $groupCatalogue  = array();

     /**
     * Load options from a file data structure
     *
     * @param string $config
     */
    public function __construct(string $config, ?int $type = null)
    {
        if ( ! is_file($config))
        {
            throw new OptionLoaderException("File $config not found");
        }

        $data = self::loadData($config, $type);

        foreach ($this->itterator($data) as $object)
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
        return array_unique($this->optionCatalogue, SORT_REGULAR);
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
     * Generator function to itterate over an array from a file.
     * Individual items are identified and given to the appropriate parser.
     * The resulting object returned by the parser is yielded.
     *
     * @param array $json
     *  an array of items from the entire json file or a part of it
     *
     * @return \Generator|GroupObject[]
     */
    private function itterator(array $data)
    {
        foreach ($data as $item)
        {
            if (self::areArrayKeysInt($item))
            {
                $this->itterator($item);
            }

            $object = self::identifyObject($item);

            yield $this->{"parse$object"}($item);
        }
    }

    /**
     * Identify whether all keys in an array are integers
     *
     * @param array $array
     *
     * @return bool
     */
    private static function areArrayKeysInt(array $array) : bool
    {
        return (
            count(
                array_filter(array_keys($array), 'is_int')
            ) === count($array)
        );
    }

    /**
     * Identify the object parser that a particular item should be handed to
     *
     * @param array $item an item that does not only contain int keys
     *
     * @return string
     *  the type of parser that should be used to parse the given item
     *
     * @throws Exception if the correc $item parser cannot be identified
     */
    private static function identifyObject(array $item) : string
    {
        if (
            isset($item['group'])
            and isset($item['members'])
            and is_array($item['members'])
            and ! isset($item['option'])
        ) {
            return "Group";
        }
        elseif (
            isset($item['option'])
            and ! isset($item['members'])
            and ! isset($item['group'])
        ) {
            return "Option";
        }
        else
        {
            throw new OptionLoaderException(
                "Could not identify object",
                $item
            );
        }
    }

    private static function loadData(string $file, ?int $type = null)
    {
        if ($type === self::JSON)
        {
            $data = json_decode(file_get_contents($file), true);

            if ( ! isset($data))
            {
                throw new OptionLoaderException(
                    "File $file does not appear to be valid JSON"
                );
            }
        }
        elseif ($type === self::YAML)
        {
            try
            {
                $data = Yaml::parse(file_get_contents($file));
            }
            catch (ParseException $e)
            {
                throw new OptionLoaderException(
                    "File $file does not appear to be valid YAML: "
                    .$e->getMessage()
                );
            }
        }
        else
        # assume $type === self:AUTO
        {
            foreach (self::EXT_TYPE_MAP as $ext => $type)
            {
                if (preg_match('/[.]'.preg_quote($ext, '/').'$/i', $file))
                {
                    $data = self::loadData($file, $type);
                }
            }

            if ( ! isset($data))
            {
                throw new OptionLoaderException(
                    "File $file does not appear to be valid"
                );
            }
        }

        return $data;
    }

    /**
     * Parse a group from file data structure
     *
     * @param array $item
     *
     * @return Group
     */
    private function parseGroup(array $item) : Group
    {
        $type = strtoupper($item['group']);

        if ( ! in_array($type, self::GROUP_TYPES))
        {
            throw new OptionLoaderException(
                "Unrecognised group type $type",
                $item
            );
        }

        $groupClass = self::GROUP_NAMESPACE."${type}Group";

        $group = new $groupClass();

        $name = $item['name'] ?? null;

        if (isset($name))
        {
            $group->setName($name);

            $this->groupCatalogue[$group->getName()] = $group;
        }

        foreach ($this->itterator($item['members']) as $object)
        {
            $group->add($object);
        }

        return $group;
    }

    /**
     * Parse an option from file data structure
     *
     * @param array $item
     *
     * @return Option
     */
    private function parseOption(array $item) : Option
    {
        $name = $item['option'];

        $characteristic = $item['characteristic'] ?? null;

        if (isset($characteristic))
        {
            $characteristic = $this->optionCharacteristic($characteristic);
        }

        $type = $item['type'] ?? null;

        $default = $item['default'] ?? null;

        $description = $item['description'] ?? null;

        $fixedName = $item['name'] ?? null;

        $option = new Option($name, $characteristic, $type, $default);

        if (isset($description))
        {
            $option->setDescription($description);
        }

        if (isset($fixedName))
        {
            if ($fixedName[0] === '-')
            {
                throw new OptionLoaderException(
                    "An option's fixed name may not start with a `-`.",
                    $item
                );
            }

            $option->setFixedName($fixedName);
        }

        if (isset($item['aliases']))
        {
            foreach ($item['aliases'] as $alias)
            {
                $aliasCharacteristic = $alias['characteristic'] ?? null;

                if (isset($aliasCharacteristic))
                {
                    $aliasCharacteristic = $this->optionCharacteristic(
                        $aliasCharacteristic
                    );
                }

                $aliasName = $alias['option'] ?? null;

                if ( ! isset($aliasName))
                {
                    throw new OptionLoaderException(
                        "An alias must contain its own option name.",
                        $alias
                    );
                }

                new AliasOption($aliasName, $aliasCharacteristic, $option);
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
