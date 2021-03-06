<?php

namespace Aidantwoods\BetterOptions;

use Aidantwoods\BetterOptions\Groups\ORGroup;
use Aidantwoods\BetterOptions\Groups\XORGroup;
use Aidantwoods\BetterOptions\Groups\ANDGroup;
use Aidantwoods\BetterOptions\Options\Option;
use Aidantwoods\BetterOptions\Options\AliasOption;

require_once('vendor/autoload.php');

# json config

$optionLoader = new OptionLoader('options.yaml');

if ($optionLoader->getOption('help')->getValue())
{
    echo $optionLoader->getHelp();
    // die();
}

if ( ! $optionLoader->getGroup('my-xor-group')->isSet())
{
    echo "my-xor-group was not set :( \n";
}

foreach ($optionLoader->getOptions() as $option)
{
    echo $option->getName() . ":\n";
    var_dump($option->getValue());
    echo "\n";
}

$responses = false;

foreach ($optionLoader->getResponseMessages() as $message)
{
    $responses = true;
    echo $message . "\n";
}



# manual config

// $group1 = (new ANDGroup())
//     ->add(
//         new AliasOption(
//             'fooalias2',
//             Option::LONG,
//             new AliasOption(
//                 'fooalias',
//                 Option::SHORT,
//                 new Option('foo', Option::LONG, 'string')
//             )
//         )
//     )
//     ->add(new Option('bar'))
//     ->add(new Option('baz', Option::SHORT, 'string[]'))
//     ;

// $options = (new ORGroup)
//     ->add(new AliasOption('boo', Option::LONG, new Option('bin')))
//     ->add($group1);