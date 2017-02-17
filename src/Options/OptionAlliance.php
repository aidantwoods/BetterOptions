<?php

namespace Aidantwoods\BetterOptions\Options;

use Aidantwoods\BetterOptions\OptionInterface;
use Aidantwoods\BetterOptions\Options\Option;
use Aidantwoods\BetterOptions\Options\AliasOption;

/**
 * A friend group so that members of the OptionAlliance may access
 * each others implementation of bindAlias
 */
abstract class OptionAlliance implements OptionInterface
{
    /**
     * Bind an alias to the root Option
     *
     * @param AliasOption $alias the alias instance to bind
     *
     * @return void
     */
    abstract protected function bindAlias(AliasOption $alias);
}