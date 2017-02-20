<?php

namespace Aidantwoods\BetterOptions;

use Exception;

class OptionLoaderException extends Exception
{
    public function __construct(string $message, $var = null)
    {
        $this->message = $message;

        $this->var = $var;
    }

    public function __toString()
    {
        return  $this->message
                . (isset($this->var) ? 
                    "\nError near: \n\n"
                    .var_export($this->var, true)
                  : ''
                );
    }
}