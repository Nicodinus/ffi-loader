<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nicodinus\FFILoader\Preprocessor\ExpressionProcessor\Exception;

use Phplrt\Contracts\Lexer\TokenInterface;

/**
 * Class UnexpectedTokenException
 */
class UnexpectedTokenException extends ExpressionProcessorException
{
    /**
     * @param TokenInterface $unexpectedToken
     *
     * @return static
     */
    public static function create(TokenInterface $unexpectedToken): self
    {
        return new static("Unexpected token \"{$unexpectedToken->getName()}\" at expression processor");
    }
    //
}
