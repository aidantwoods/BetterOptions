<?php

namespace Aidantwoods\BetterOptions;

interface Option extends GroupObject
{
    const LONG       = 0b001;
    const SHORT      = 0b010;
    const POSITIONAL = 0b100;

    const CHARACTERISTICS = array(
        'LONG'       => self::LONG,
        'SHORT'      => self::SHORT,
        'POSITIONAL' => self::POSITIONAL
    );

    /**
     * Get the option name
     *
     * @return string return the option name
     */
    public function getName() : string;

    /**
     * Get the printable option name with dashes (as specified by
     * the characteristic setting)
     *
     * @return string return the printable option name
     */
    public function getPrintableName() : string;

    /**
     * Get the option value
     *
     * @return mixed return a value of the type specified by {@see getType}
     */
    public function getValue();

    /**
     * Get the characteristic
     *
     * @return integer return an integer from the self::CHARACTERISTICS array
     */
    public function getCharacteristic() : int;

    /**
     * Get the type as set in construct
     *
     * @return string return a lowercase string specifying the option
     * value type
     */
    public function getType() : string;

    /**
     * Get the option default value
     *
     * @return mixed return a value of the type specified by {@see getType}
     */
    public function getDefault();

    /**
     * Set the options value
     *
     * @param mixed $value set a value of the type returned by {@see getType}.
     *  record this event such that {@see isSet} will return true
     *
     * @return mixed return a value of the type returned by {@see getType}
     */
    public function setValue($value);

    /**
     * Check whether the option has been set
     *
     * @return bool return true if the option has been set via
     *  {@see setValue}, false otherwise
     */
    public function isSet() : bool;

    /**
     * Option may offer a response to its unset state
     *
     * @return Response[]
     */
    public function respond() : array;
}
