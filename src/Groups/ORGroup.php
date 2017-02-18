<?php

namespace Aidantwoods\BetterOptions\Groups;

use Exception;
use Aidantwoods\BetterOptions\Group;
use Aidantwoods\BetterOptions\Option;
use Aidantwoods\BetterOptions\Response;
use Aidantwoods\BetterOptions\GroupObject;

class ORGroup implements Group
{
    private $objects = array(),
            $name;

    /**
     * Add an object to the group
     *
     * @param GroupObject &$object the object to add
     *
     * @return Group return the current group instance
     */
    public function add(GroupObject $object) : Group
    {
        $this->objects[] = $object;

        return $this;
    }

    /**
     * Get objects in a group
     *
     * @return GroupObject[]
     *  return an array of GroupObject objects
     */
    public function get() : array
    {
        return $this->objects;
    }

    /**
     * Give the group a name so that it may be easily retrieved
     *
     * @param string $name the group name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * Get the group a name
     *
     * @return string the group name
     */
    public function getName() : ?string
    {
        return $this->name;
    }

    /**
     * Validate the group is complete.
     * A group is responsible for defining its own completeness 
     *
     * @return bool true if the group is complete, false otherwise
     */
    public function isSet() : bool
    {
        if (count($this->objects) < 1)
        {
            throw new Exception("ORGroup contains too few objects");
        }

        foreach ($this->objects as $object)
        {
            if ($object->isSet())
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Object may offer a response to its unset state. An object MUST return
     * the responses of its members when their unset state contributes to its
     * unset state. An object SHOULD nest member responses.
     *
     * @return Response[] return an array of Response objects, or Response
     * objects nested in another array
     */
    public function respond() : array
    {
        $responses = array();

        if ( ! $this->isSet())
        {
            $responses[] = new Response(
                'At least one of '.$this->getPrintableName().' must be set'
            );

            $memberResponses = array();

            foreach ($this->objects as $object)
            {
                $memberResponses[] = $object->respond();
            }

            // $responses[] = $memberResponses;
        }

        return $responses;
    }

    /**
     * Get the printable names of all members, and return these delimited in
     * a linguistically interpretable fashion, enclosed between curly braces 
     *
     * @return string return the printable group representation
     */
    public function getPrintableName() : string
    {
        $printables = array();

        foreach ($this->objects as $object)
        {
            $printables[] = $object->getPrintableName();
        }

        return '{'.implode(' or ', $printables).'}';
    }
}
