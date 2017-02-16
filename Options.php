<?php

namespace Aidantwoods\BetterOptions;

use Aidantwoods\BetterOptions\Groups\ORGroup;
use Aidantwoods\BetterOptions\Groups\XORGroup;
use Aidantwoods\BetterOptions\Groups\ANDGroup;
use Aidantwoods\BetterOptions\Options\RealOption;

require_once('vendor/autoload.php');

$options = (new ORGroup())
    ->addObject(new RealOption('foo', Option::LONG, 'string'))
    ->addObject(new RealOption('bar'));

var_dump($options->respond());

var_dump($options->getObjects());