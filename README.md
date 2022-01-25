# FFI Loader

***UNDER REFACTORING (use `master` branch instead)***

- **[ + ]** Preprocessor
- **[ ‒ ]** Library wrappers & FFI

## Requirements

- PHP >= 7.4

## Installation

```sh
$ composer require nicodinus/php-ffi-loader
```
## Preprocessor

The implementation of the preprocessor is not completely equivalent to the preprocessor C and only supports a simple set of instructions to speed up parsing.

### Example:
```php
use Nicodinus\FFILoader\Preprocessor\AbstractPreprocessor;
use Nicodinus\FFILoader\Preprocessor\Exception\IncludeException;

$preprocessor = new class extends AbstractPreprocessor {
    /**
     * @inheritDoc
     */
    public function isCommentsPreserved(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isErrorsSkipEnabled(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isMinifyEnabled(): bool
    {
        return true;
    }
    
    /**
     * @param string $path
     *
     * @return string
     *
     * @throws IncludeException
     */
    protected function readGlobalInclude(string $path): string
    {
        // TODO: Implement readGlobalInclude() method.
    }

    /**
     * @param string $path
     *
     * @return string
     *
     * @throws IncludeException
     */
    protected function readLocalInclude(string $path): string
    {
        // TODO: Implement readLocalInclude() method.
    }
    
    /**
     * @inheritDoc
     */
    protected function print(string $message): void
    {
        \fputs(\STDOUT, $message . \PHP_EOL);
    }
    
    /**
     * Zero value equals infinity.
     * Negative will disable including operation.
     *
     * @return int
     */
    protected function getMaxIncludeDepth(): int
    {
        return parent::getMaxIncludeDepth();
    }
}

//

$defines = [
    'var1' => 'Hello',
];

$sourceCode = <<<'SOURCE_CODE'

#define var2 World
#print ${var1} ${var2}

// SOURCE CODE HERE

SOURCE_CODE;

$result = $preprocessor->execute($sourceCode, $defines);
var_dump($defines);
var_dump($result);
```

**Output:**
```sh
Hello World
array(2) {
  ["var1"]=>
  string(5) "Hello"
  ["var2"]=>
  string(5) "World"
}
string(21) "
// SOURCE CODE HERE
"
```

### Defines

* `#define NAME VALUE` (like `#define var1 define var1 value` will define as `define var1 value`)
* Can using defines at all source code bellow define with `${var1}` directive (will replace it to `value` from `#define var1 value`)
* `#ifdef var1` or `#ifndef var1` (define expressions)
* `#undef var1` (remove define `var1`)

**Re-definition is not allowed (will throw `InvalidDefineTokenOperationException::class` exception)**

#### Environment defines

```sh
#print ${OS} ${ARCH}
```

`${OS}` can be:
* `windows`
* `linux`
* `darwin` (OSX, macOS, Apple)
* `bsd`
* `solaris`
* `undefined`

`${ARCH}` can be:
* `amd64`
* `i386`
* `undefined`

### Expressions

* `#if a > b` - greater than
* `#if a >= b` - greater or equal
* `#if a < b` - less than
* `#if a <= b` - less or equal
* `#if a != b` or `#if a <> b` - not equal
* `#if a == b` - equal

#### Types

* Integer (`42`)
* Float (`4.2`)
* Boolean (`true` or `false`)
* Version (like `1.0.0` or `0.0.1-dev`)
* Strings (`"string 1"` or `'string 2'`)
* Can interact with definitions (`#if ${var1} > ${var2}`)

**All strings presented as `strtolower(trim(" QwErTy ")) === "qwerty"`
Strings expressions allows only equal (`==`) and not-equal (`!=` `<>`)**

### Includes

* `#include "local_include_file"` - calls `AbstractPreprocessor::readLocalInclude` function
* `#include <global_include_file>` - calls `AbstractPreprocessor::readGlobalInclude` function

**Care about *deadlock/cyclic* includes (when source1.h has `#include "source1.h"`). There is default include depth limit `256` (can be managed by overriding function `getMaxIncludeDepth` in your `Preprocessor` class)**

### Comments
```sh
// Comment

/*

Comment

*/
```
