<?php

namespace Aidantwoods\BetterOptions\Options;

use Exception;
use Aidantwoods\BetterOptions\Type;
use Aidantwoods\BetterOptions\Option;
use Aidantwoods\BetterOptions\Response;

class RealOption implements Option
{
    private $name,
            $value,
            $characteristic,
            $type,
            $default,
            $set;

    public function __construct(
        string $name,
        int    $characteristic = Option::SHORT,
       ?string $type = 'bool',
        string $default = null
    ) {
        $this->name = $name;

        if ( ! in_array($characteristic, self::CHARACTERISTICS, true))
        {
            throw new Exception(
                "The characteristic for option named $name is not valid.\n"
                . "Valid types: "
                . implode(
                    "\n\tOption::",
                    array_flip(self::CHARACTERISTICS)
                )
            );
        }

        $this->characteristic = $characteristic;

        $this->type = Type::normalise($type, true);

        if ( ! Type::is($default, $type))
        {
            throw new Exception(
                "The default for option named $name is not of the specified "
                . "type $type."
            );
        }

        $this->value = $this->default = $default;
    }

    /**
     * Get the option name
     *
     * @return string return the option name
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Get the printable option name with dashes (as specified by
     * the characteristic setting)
     *
     * @return string return the printable option name
     */
    public function getPrintableName() : string
    {
        $characteristic = $this->getCharacteristic();

        if ($characteristic === Option::LONG)
        {
            $prefix = '--';
        }
        elseif ($characteristic === Option::SHORT)
        {
            $prefix = '-';
        }
        elseif ($characteristic === Option::POSITIONAL)
        {
            $prefix = '';
        }

        return $prefix.$this->name;
    }

    /**
     * Get the option value
     *
     * @return mixed return a value of the type specified by {@see getType}
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the characteristic
     *
     * @return integer return an integer from the self::CHARACTERISTICS array
     */
    public function getCharacteristic() : int
    {
        return $this->characteristic;
    }

    /**
     * Get the type as set in construct
     *
     * @return string return a lowercase string specifying the option
     * value type
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * Get the option default value
     *
     * @return mixed return a value of the type specified by {@see getType}
     */
    public function getDefault()
    {
        return $this->default;
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
        if (Type::hasOuterArray($this->type) or $this->type === 'array')
        {
            if ( ! $this->isSet())
            {
                $this->value = array();
            }

            $this->value[] = ($this->type !== 'array' ?
                Type::cast($value, $this->type)
                : $value
            );
        }

        $this->value = Type::cast($value, $this->type);

        $this->set = true;
    }

    /**
     * Check whether the option has been set
     *
     * @return bool return true if the option has been set via
     *  {@see setValue}, false otherwise
     */
    public function isSet() : bool
    {
        return $this->set === true;
    }

    /**
     * Option may offer a response to its unset state
     *
     * @return Response[]
     */
    public function respond() : array
    {
        return array();
    }
}
