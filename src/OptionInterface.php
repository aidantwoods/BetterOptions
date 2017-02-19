<?php

namespace Aidantwoods\BetterOptions;

/**
 * An OptionInterface's constructor is responsible for ensuring that the
 * option is set
 */
interface OptionInterface extends GroupObject
{
    const LONG       = 0b01;
    const SHORT      = 0b10;

    const CHARACTERISTICS = array(
        'LONG'       => self::LONG,
        'SHORT'      => self::SHORT
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
     * Set the option's fixed name
     *
     * @param string $name
     */
    public function setFixedName(string $name);

    /**
     * Get the option's fixed name
     *
     * @return string the fixed name
     */
    public function getFixedName() : string;

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
     * Set the option's value
     *
     * @param mixed $value set a value of the type returned by {@see getType}.
     *  record this event such that {@see isSet} will return true
     */
    public function setValue($value);

    /**
     * Set a description for the option
     *
     * @param string $description the description
     */
    public function setDescription(string $description);

    /**
     * Get the description of the option
     *
     * @return ?string $description the description
     */
    public function getDescription() : ?string;

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
