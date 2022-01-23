<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nicodinus\FFILoader\Preprocessor\Lexer\Exception;

/**
 * Class UnexpectedLexemeException
 */
class UnexpectedLexemeException extends LexerException
{
    /**
     * @param string $unexpectedLexemeName
     *
     * @return static
     */
    public static function create(string $unexpectedLexemeName): self
    {
        return new static("Unexpected lexeme \"{$unexpectedLexemeName}\"");
    }
}
