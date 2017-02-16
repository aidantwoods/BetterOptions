<?php

namespace Aidantwoods\BetterOptions;

class Response
{
    private $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function getMessage() : string
    {
        return $this->message;
    }
}