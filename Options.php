<?php

namespace Aidantwoods\BetterOptions;

use Aidantwoods\BetterOptions\Groups\ORGroup;
use Aidantwoods\BetterOptions\Groups\XORGroup;
use Aidantwoods\BetterOptions\Groups\ANDGroup;
use Aidantwoods\BetterOptions\Options\Option;
use Aidantwoods\BetterOptions\Options\AliasOption;

require_once('vendor/autoload.php');

$objects = new OptionLoader('options.json');

var_dump($objects);

die();

$group1 = (new ANDGroup())
    ->add(
        new AliasOption(
            'fooalias2',
            Option::LONG,
            new AliasOption(
                'fooalias',
                Option::SHORT,
                new Option('foo', Option::LONG, 'string')
            )
        )
    )
    ->add(new Option('bar'))
    ->add(new Option('baz', Option::SHORT, 'string[]'))
    ;

$options = (new ORGroup)
    ->add(new AliasOption('boo', Option::LONG, new Option('bin')))
    ->add($group1);

if ($options->isSet())
{
    echo 'A-ok'."\n";

    $recursiveEcho = function($objects) use (&$recursiveEcho)
    {
        foreach ($objects as $object)
        {
            if ($object instanceof Group)
            {
                $recursiveEcho($object->get());
            }
            elseif ($object instanceof OptionInterface)
            {
                echo $object->getName(). ":\t";
                var_dump($object->getValue());
            }
        }
    };

    $recursiveEcho($options->get());
}
else
{
    $echoResponses = function($responses) use (&$echoResponses)
    {
        foreach ($responses as $response)
        {
            if (is_array($response))
            {
                $echoResponses($response);
            }

            else
            {
                echo $response->getMessage() . "\n";
            }
        }
    };

    $echoResponses($options->respond());
}