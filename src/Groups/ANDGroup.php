<?php

namespace Aidantwoods\BetterOptions\Groups;

use Exception;
use Aidantwoods\BetterOptions\Group;
use Aidantwoods\BetterOptions\Option;
use Aidantwoods\BetterOptions\Response;
use Aidantwoods\BetterOptions\GroupObject;

class ANDGroup implements Group
{
    private $objects = array();

    /**
     * Add an object to the group
     *
     * @param GroupObject &$object the object to add
     *
     * @return Group return the current group instance
     */
    public function addObject(GroupObject $object) : Group
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
    public function getObjects() : array
    {
        return $this->objects;
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
            throw new Exception("ANDGroup contains too few objects");
        }

        $i = 0;

        foreach ($this->objects as $object)
        {
            if ($object->isSet())
            {
                $i++;
            }
        }

        return count($this->objects) === $i;
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
                $this->getPrintableName().' must all be set'
            );
            
            $memberResponses = array();

            foreach ($this->objects as $object)
            {
                $memberResponses[] = $object->respond();
            }

            $responses[] = $memberResponses;
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

        return '{'.implode(' and ', $printables).'}';
    }
}

