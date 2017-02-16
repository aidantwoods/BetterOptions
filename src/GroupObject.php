<?php

namespace Aidantwoods\BetterOptions;

interface GroupObject
{
    /**
     * Determine whether the object is set 
     *
     * @return bool
     */
    public function isSet() : bool;

    /**
     * Generate and return a printable representation of the object
     *
     * @return string
     */
    public function getPrintableName() : string;

    /**
     * Object may offer a response to its unset state. An object MUST return
     * the responses of its members when their unset state contributes to its
     * unset state. An object SHOULD nest member responses.
     *
     * @return Response[] return an array of Response objects, or Response
     * objects nested in another array
     */
    public function respond() : array;
}