<?php

namespace Aidantwoods\BetterOptions\Options;

use Exception;
use Aidantwoods\BetterOptions\Type;
use Aidantwoods\BetterOptions\OptionInterface;
use Aidantwoods\BetterOptions\Response;
use Aidantwoods\BetterOptions\OptionParser;

class Option extends OptionAlliance implements OptionInterface
{
    private $name,
            $fixedName,
            $value,
            $characteristic,
            $type,
            $default,
            $set,
            $aliases = array(),
            $description;

    public function __construct(
        string $name,
       ?int    $characteristic = Option::LONG,
       ?string $type = 'bool',
        string $default = null
    ) {
        $characteristic = $characteristic ?? Option::LONG;

        $type = $type ?? 'bool';

        $this->name = $name;

        if ( ! in_array($characteristic, self::CHARACTERISTICS, true))
        {
            throw new Exception(
                "The characteristic for option named $name is not valid.\n"
                . "Valid types: "
                ."\n\tOption::"
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

        OptionParser::bindValue($this);
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

        return $prefix.$this->name;
    }

    /**
     * Set the option's fixed name
     *
     * @param string $name
     */
    public function setFixedName(string $name)
    {
        $this->fixedName = $name;
    }

    /**
     * Get the option's fixed name
     *
     * @return string the fixed name
     */
    public function getFixedName() : string
    {
        return $this->fixedName;
    }

    /**
     * Get the option value
     *
     * @return mixed return a value of the type specified by {@see getType}
     */
    public function getValue()
    {
        if ( ! $this->isSet() and isset($this->default))
        {
            $this->setValue($this->default, true);
        }

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
     * Set the option's value
     *
     * @param mixed $value set a value of the type returned by {@see getType}.
     *  record this event such that {@see isSet} will return true
     * @param bool $preserveSetStatus whether to set without changing the value
     *  returned by {@see isSet}
     */
    public function setValue($value, bool $preserveSetStatus = false)
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
        else
        {
            $this->value = Type::cast($value, $this->type);
        }

        if ( ! $preserveSetStatus)
        {
            $this->set = true;
        }
    }

    /**
     * Set a description for the option
     *
     * @param string $description the description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * Get the description of the option
     *
     * @return ?string $description the description
     */
    public function getDescription() : ?string
    {
        return $this->description;
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

    public function getAliasNames() : array
    {
        $names = array();

        foreach ($this->aliases as $alias)
        {
            $names[] = $alias->getPrintableName();
        }

        return $names;
    }

    /**
     * Bind an alias to the root Option
     *
     * @param AliasOption $alias the alias instance to bind
     *
     * @return void
     */
    protected function bindAlias(AliasOption $alias)
    {
        $this->aliases[] = $alias;
    }
}
