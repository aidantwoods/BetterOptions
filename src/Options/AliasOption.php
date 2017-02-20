<?php

namespace Aidantwoods\BetterOptions\Options;

use Aidantwoods\BetterOptions\OptionInterface;
use Aidantwoods\BetterOptions\OptionParser;

class AliasOption extends OptionAlliance implements OptionInterface
{
    private $name,
            $characteristic,
            $option;

    /**
     * Construct the alias such that appropriate method calls are rerouted,
     * and bind this instance to the root alias
     */
    public function __construct(
        string $name,
       ?int $characteristic = Option::SHORT,
        OptionAlliance $option
    ) {
        $characteristic = $characteristic ?? Option::SHORT;

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

        $this->name   = $name;
        $this->option = $option;

        $this->bindAlias($this);

        OptionParser::bindValue($this);
    }

    /**
     * Get the option name
     *
     * @return string return the option name
     */
    public function getName() : string
    {
        return $this->option->getName();
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
     * Set the option's fixed name
     *
     * @param string $name
     */
    public function setFixedName(string $name)
    {
        return $this->option->setFixedName($name);
    }

    /**
     * Get the option's fixed name
     *
     * @return string the fixed name
     */
    public function getFixedName() : string
    {
        return $this->option->getFixedName();
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
     * Set the option's value
     *
     * @param mixed $value set a value of the type returned by {@see getType}.
     *  record this event such that {@see isSet} will return true
     * @param bool $preserveSetStatus whether to set without changing the value
     *  returned by {@see isSet}
     */
    public function setValue($value, bool $preserveSetStatus = false)
    {
        return $this->option->setValue($value, $preserveSetStatus);
    }

    /**
     * Set a description for the option
     *
     * @param string $description the description
     */
    public function setDescription(string $description)
    {
        $this->option->setDescription($description);
    }

    /**
     * Get the description of the option
     *
     * @return ?string $description the description
     */
    public function getDescription() : ?string
    {
        return $this->option->getDescription();
    }

    /**
     * Check whether the option has been set
     *
     * @return bool return true if the option has been set via
     *  {@see setValue}, false otherwise
     */
    public function isSet() : bool
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
        return $this->option->respond();
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
        $this->option->bindAlias($alias);
    }
}
