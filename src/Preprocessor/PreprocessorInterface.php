<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader\Preprocessor;

use Serafim\FFILoader\Preprocessor\Exception\ExpressionTokenException;
use Serafim\FFILoader\Preprocessor\Exception\IncludeDisabledException;
use Serafim\FFILoader\Preprocessor\Exception\IncludeException;
use Serafim\FFILoader\Preprocessor\Exception\IncludeMaxDepthReachedException;
use Serafim\FFILoader\Preprocessor\Exception\InvalidDefineTokenDefinitionException;
use Serafim\FFILoader\Preprocessor\Exception\InvalidDefineTokenOperationException;

/**
 * Class PreprocessorInterface
 */
interface PreprocessorInterface
{
    /**
     * @param string $sourceCode
     * @param array $defines
     *
     * @return string
     *
     * @throws IncludeMaxDepthReachedException
     * @throws InvalidDefineTokenDefinitionException
     * @throws InvalidDefineTokenOperationException
     * @throws IncludeDisabledException
     * @throws ExpressionTokenException
     * @throws IncludeException
     */
    public function execute(string $sourceCode, array &$defines = []): string;

    /**
     * @param string $expression
     *
     * @return bool
     *
     * @throws ExpressionTokenException
     */
    public function executeExpression(string $expression): bool;

    /**
     * @return bool
     */
    public function isCommentsPreserved(): bool;

    /**
     * @return bool
     */
    public function isErrorsSkipEnabled(): bool;

    /**
     * @return bool
     */
    public function isMinifyEnabled(): bool;
}
