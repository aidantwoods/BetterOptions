<?php

namespace Aidantwoods\BetterOptions;

interface Group extends GroupObject
{
    /**
     * Add an object to the group
     *
     * @param GroupObject &$object the object to add
     *
     * @return Group return the current group instance
     */
    public function add(GroupObject $object) : Group;

    /**
     * Get objects in a group
     *
     * @return GroupObject[]
     *  return an array of GroupObject objects
     */
    public function get() : array;

    /**
     * Give the group a name so that it may be easily retrieved
     *
     * @param string $name the group name
     */
    public function setName(string $name);

    /**
     * Get the group a name
     *
     * @return string the group name
     */
    public function getName() : ?string;

    /**
     * Validate the group is complete.
     * A group is responsible for defining its own completeness 
     *
     * @return bool true if the group is complete, false otherwise
     */
    public function isSet() : bool;

    /**
     * Group may offer a response to its unset state. An object MUST return
     * the responses of its members
     *
     * @return Response[] return an array of Response objects
     */
    public function respond() : array;

    /**
     * Get the printable names of all members, and return these delimited in
     * a linguistically interpretable fashion, enclosed between curly braces 
     *
     * @return string return the printable group representation
     */
    public function getPrintableName() : string;
}
