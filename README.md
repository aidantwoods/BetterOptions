# BetterOptions
*than [`getopt()`](http://php.net/manual/en/function.getopt.php)*

## Synopsis
`getopt()` sucks. Flags (booleans – options with no value) are set as false
**when they *are* set**,
options that do not exist will cause a key error if you attempt to look them up.
If multiple options are specified you are given an array containing them all,
regardless of whether you wanted to recieve an array.

There is no way to specify which type you are expecting. If compulsory options
are left unspecified by the user, this will prevent other compulsory options
from being parsed, (but will not enforce that these options are present).
"long" options are specified in an array, while "short" options are specified
as a string...

All this means that in order to accept command line options, lots (and lots) of
ugly validation logic must be manually written.

---

Instead, create a json file specifying logical (AND, OR, XOR) requirement groups
of options, and/or just specify options on their own with no requirements.

The expected type of an option can be specified, and this is the type you will
recieve (or `null` if not set).

If you're not expecting multiple options you won't get an array. If you want to
accept multiple options, but can cope with one just fine you can recieve an
array with one item in (types like `string[]`, `bool[]`, `integer[]` are arrays
containing a certain type).

You can document your options in the json file too, as well as specifying
whether you want a 'long' or 'short' (two or one dash) options without changing
the structure of specification.

Aliases can also be defined (e.g. `--file`, `-f`). You can make as many as you
like (though two is almost always sufficient).

You can recieve responses from groups if their logical conditions fail so you
don't have to write error messages yourself, and you can even use an
auto-generated help screen populated with the description of each option from
the json file.

## Example
Using the command line options configured in the example `options.json`,
and the code in `example.php`, the following can be achieved:

```bash
$ php example.php --foo=asda --foo=sdsg --bar=dsgs
```

```php
foo:
array(2) {
  [0]=>
  string(4) "asda"
  [1]=>
  string(4) "sdsg"
}

bar:
string(4) "dsgs"

baz:
NULL

boo:
NULL

help:
NULL
```

If we change the type of --foo from `string[]` to `string` in `options.json`,
we can instead get

```php
foo:
string(4) "asda"
```

(the first value configured is returned).

If we just call

```bash
$ php example.php
```

then the auto-generated response for incompleteness (based on logical groups
setup in the json file) is as follows

```
At least one of {--foo or --bar or {--baz xor --boo}} must be set
```

Using the auto-generated help screen (we have bound this to `--help` in the
example code), we can use:

```bash
$ php example.php --help
```
to get the following
```
--foo, -fooalias        Foo.
--bar
--baz                   Lorem ipsum dolor sit amet, consectetur adipiscing
                        elit. Ut fermentum tristique enim quis consequat.
                        Sed dignissim mi in erat gravida, sed interdum
                        mauris iaculis. Quisque nulla dolor, sollicitudin
                        quis pretium non, venenatis eu tortor. Mauris eget
                        enim leo. Etiam pretium semper leo, eu elementum
                        lacus luctus in. Cras elit ex, rhoncus ac gravida
                        ac, pulvinar porttitor sapien. Ut vel purus
                        sollicitudin, finibus est ac, imperdiet elit. Ut
                        sollicitudin at nunc et dapibus.

--boo
--help                  Print this help dialogue
```


