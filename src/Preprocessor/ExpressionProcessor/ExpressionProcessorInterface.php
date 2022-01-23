<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nicodinus\FFILoader\Preprocessor\ExpressionProcessor;

use Nicodinus\FFILoader\Preprocessor\ExpressionProcessor\Exception\NotEnoughTokensException;
use Nicodinus\FFILoader\Preprocessor\ExpressionProcessor\Exception\UnexpectedTokenException;
use Nicodinus\FFILoader\Preprocessor\Lexer\Exception\UnexpectedLexemeException;

/**
 * Class ExpressionProcessorInterface
 */
interface ExpressionProcessorInterface
{
    /**
     * @param string $expression
     *
     * @return bool
     *
     * @throws UnexpectedLexemeException
     * @throws UnexpectedTokenException
     * @throws NotEnoughTokensException
     */
    public function execute(string $expression): bool;
}
