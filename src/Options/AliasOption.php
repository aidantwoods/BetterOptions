<?php

namespace Aidantwoods\BetterOptions\Options;

use Aidantwoods\BetterOptions\Option;

class AliasOption implements Option
{
    private $name,
            $option;

    /**
     * Get the option name
     *
     * @return string return the option name
     */
    public function __construct(string $name, Option &$option)
    {
        $this->name   = $name;
        $this->option = $option;
    }

    /**
     * Get the printable option name with dashes (as specified by
     * the characteristic setting)
     *
     * @return string return the printable option name
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Get the option value
     *
     * @return mixed return a value of the type specified by {@see getType}
     */
    public function getValue()
    {
        return $this->option->getValue();
    }

    /**
     * Get the characteristic
     *
     * @return integer return an integer from the self::CHARACTERISTICS array
     */
    public function getCharacteristic() : integer
    {
        return $this->option->getCharacteristic();
    }

    /**
     * Get the type as set in construct
     *
     * @return string return a lowercase string specifying the option
     * value type
     */
    public function getType() : string
    {
        return $this->option->getType();
    }

    /**
     * Get the option default value
     *
     * @return mixed return a value of the type specified by {@see getType}
     */
    public function getDefault()
    {
        return $this->option->getDefault();
    }

    /**
     * Set the options value
     *
     * @param mixed $value set a value of the type returned by {@see getType}.
     *  record this event such that {@see isSet} will return true
     *
     * @return mixed return a value of the type returned by {@see getType}
     */
    public function setValue($value)
    {
        return $this->option->setValue($value);
    }

    /**
     * Check whether the option has been set
     *
     * @return bool return true if the option has been set via
     *  {@see setValue}, false otherwise
     */
    public function isSet() : boolean
    {
        return $this->option->isSet();
    }

    /**
     * Option may offer a response to its unset state
     *
     * @return Response[]
     */
    public function respond() : array
    {
        return $this->option->isSet();
    }
}
