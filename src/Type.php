<?php

namespace Aidantwoods\BetterOptions;

use Exception;

abstract class Type
{
    const MAP = array(
        'array'   => 'array',

        'boolean' => 'boolean',
        'bool'    => 'boolean',

        'string'  => 'string',

        'integer' => 'integer',
        'int'     => 'integer'
    );

    /**
     * Determine whether $variable is of type $expectedType
     *
     * @param mixed $variable the variable to check
     * @param string $expectedType the type to check for
     *
     * @return bool return true if the variable of of type $expectedType
     *  return false otherwise
     *
     * @throws Exception
     */
    public static function is($variable, string $expectedType) : bool
    {
        $expectedType = self::normalise($expectedType);

        $type = gettype($variable);

        return (
            $type === 'NULL'
            or $expectedType === $type
        );
    }

    /**
     * Determine whether $type is an array containing a particular type
     *
     * @param $type the type
     *
     * @return bool return true if the variable type contains an outer array
     *
     * @throws Exception
     */
    public static function hasOuterArray(string $type) : bool
    {
        $type = self::normalise($type, true);

        return (substr($type, -2) === '[]');
    }

    /**
     * Normalise $type
     *
     * @param string $type the type to normalise
     * @param bool $keepExt keep the array extension, e.g. string[]
     *
     * @return string the normalised type
     *
     * @throws Exception
     */
    public static function normalise(string $type, bool $keepExt = false) : string
    {
        $type = strtolower($type);

        $type = preg_replace('/\s++/', '', $type);

        $arrayExt = false;

        if (preg_match('/^(\w*+)\[\]/', $type, $match))
        {
            $type = $match[1];

            $arrayExt = true;
        }

        self::assertValid($type);

        return self::MAP[$type].($keepExt and $arrayExt ? '[]' : '');
    }

    /**
     * Cast $variable to type $type
     *
     * @param mixed $variable the variable to cast
     * @param string $type the type to cast to
     *
     * @return mixed return a value of type $type
     *
     * @throws Exception
     */
    public static function cast($variable, string $type)
    {
        $type = self::normalise($type);

        if ( ! method_exists(self, "cast_$type"))
        {
            throw new Exception(
                "Type $type could not be casted: method not implemented."
            );
        }

        return self::{"cast_$type"}($variable);
    }

    /**
     * Cast $variable to a string
     *
     * @param mixed $variable the variable to cast
     *
     * @return string
     */
    private static function cast_string($variable) : string
    {
        return (string) $variable;
    }

    /**
     * Cast $variable to a bool
     *
     * @param mixed $variable the variable to cast
     *
     * @return bool
     */
    private static function cast_boolean($variable) : bool
    {
        return (boolean) $variable;
    }

    /**
     * Cast $variable to an int
     *
     * @param mixed $variable the variable to cast
     *
     * @return int
     */
    private static function cast_integer($variable) : int
    {
        return (integer) $variable;
    }

    /**
     * Assert that 
     *
     * @param string $expectedType the variable to assert the type of
     *
     * @return void
     *
     * @throws Exception
     */
    public static function assertValid(string $type) : void
    {
        if ( ! array_key_exists($type, self::MAP))
        {
            throw new Exception(
                "The entered type $type is not valid.\n"
                . "Valid types: "
                . implode(
                    "\n\t",
                    array_unique(array_flip(self::MAP))
                )
            );
        }
    }
}