<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader\Preprocessor;

use Phplrt\Contracts\Lexer\LexerInterface;
use Serafim\FFILoader\Preprocessor\Exception\ExpressionTokenException;
use Serafim\FFILoader\Preprocessor\Exception\IncludeDisabledException;
use Serafim\FFILoader\Preprocessor\Exception\IncludeException;
use Serafim\FFILoader\Preprocessor\Exception\IncludeMaxDepthReachedException;
use Serafim\FFILoader\Preprocessor\Exception\InvalidDefineTokenDefinitionException;
use Serafim\FFILoader\Preprocessor\Exception\InvalidDefineTokenOperationException;
use Serafim\FFILoader\Preprocessor\ExpressionProcessor\Exception\NotEnoughTokensException;
use Serafim\FFILoader\Preprocessor\ExpressionProcessor\Exception\UnexpectedTokenException;
use Serafim\FFILoader\Preprocessor\ExpressionProcessor\ExpressionProcessor;
use Serafim\FFILoader\Preprocessor\ExpressionProcessor\ExpressionProcessorInterface;
use Serafim\FFILoader\Preprocessor\Lexer\Exception\UnexpectedLexemeException;
use Serafim\FFILoader\Preprocessor\Lexer\Lexer;

/**
 * Class AbstractPreprocessor
 */
abstract class AbstractPreprocessor implements PreprocessorInterface
{
    /** @var ExpressionProcessorInterface */
    private ExpressionProcessorInterface $expressionProcessor;

    /** @var LexerInterface */
    private LexerInterface $lexer;

    //

    /**
     * AbstractPreprocessor constructor.
     *
     * @param ExpressionProcessorInterface|null $expressionProcessor
     * @param LexerInterface|null $sourceCodeLexer
     */
    public function __construct(ExpressionProcessorInterface $expressionProcessor = null, LexerInterface $sourceCodeLexer = null)
    {
        $this->expressionProcessor = $expressionProcessor ?? new ExpressionProcessor();
        $this->lexer = $sourceCodeLexer ?? new Lexer();
    }

    /**
     * @inheritDoc
     */
    public function execute(string $sourceCode, array &$defines = []): string
    {
        return $this->_execute($sourceCode, $defines);
    }

    /**
     * @param string $sourceCode
     * @param array $defines
     * @param int $currentIncludeDepth
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
    protected function _execute(string $sourceCode, array &$defines = [], int $currentIncludeDepth = 0): string
    {
        if ($this->getMaxIncludeDepth() > 0 && $currentIncludeDepth > $this->getMaxIncludeDepth()) {
            throw new IncludeMaxDepthReachedException("Max include depth reached!");
        }

        $generatedCode = "";
        $expressionLevel = 0;
        $skipUntil = null;

        foreach ($this->lexer->lex($sourceCode, 0) as $token)
        {
            //dump($token);

            if ($skipUntil) {
                if (!in_array($token->getName(), $skipUntil)) {
                    continue;
                }
                $skipUntil = null;
            }

            $tokenValue = $token->getValue();
            $tokenValue = \str_replace(\array_map(fn ($v) => '${' . $v . '}', \array_keys($defines)), \array_values($defines), $tokenValue);

            switch ($token->getName())
            {
                case 'T_PRINT':

                    $this->print(\trim($tokenValue));

                    break;

                case 'T_DEFINE':

                    $m = [];
                    \preg_match('/^\s*?(.*)(?:\s+?(.*)\s*?)?$/isU', $tokenValue, $m);
                    switch (\sizeof($m))
                    {
                        case 2:
                            $defineName = $m[1];
                            $defineValue = null;
                            break;
                        case 3:
                            $defineName = $m[1];
                            $defineValue = $m[2];
                            break;
                        default:
                            throw new InvalidDefineTokenDefinitionException("Invalid define token definition!");
                    }

                    if (isset($defines[$defineName])) {
                        throw new InvalidDefineTokenOperationException("Attempt to re-define {$defineName}!");
                    }

                    $defines[$defineName] = $defineValue;

                    break;

                case 'T_UNDEF':

                    $defineName = \trim($tokenValue);

                    if (!isset($defines[$defineName])) {
                        throw new InvalidDefineTokenOperationException("Attempt to un-define {$defineName}!");
                    }

                    unset($defines[$defineName]);

                    break;

                case 'T_IFDEF':

                    $expressionLevel += 1;

                    $defineName = \trim($tokenValue);

                    if (!isset($defines[$defineName])) {
                        $skipUntil = ['T_ELSEIF', 'T_ELSE', 'T_ENDIF'];
                    }

                    break;

                case 'T_IFNDEF':

                    $expressionLevel += 1;

                    $defineName = \trim($tokenValue);

                    if (isset($defines[$defineName])) {
                        $skipUntil = ['T_ELSEIF', 'T_ELSE', 'T_ENDIF'];
                    }

                    break;

                case 'T_GROUP_COMMENT':
                case 'T_COMMENT':

                    if ($this->isCommentsPreserved()) {
                        $generatedCode .= $tokenValue;
                    }

                    break;

                case 'T_NEW_LINE':
                case 'T_SOURCE':

                    $generatedCode .= $tokenValue;
                    break;

                case 'T_LOCAL_INCLUDE':

                    if ($this->getMaxIncludeDepth() < 1) {
                        throw new IncludeDisabledException("Includes is not allowed!");
                    }

                    $generatedCode .= $this->_execute($this->readLocalInclude(\trim($tokenValue)), $defines, $currentIncludeDepth + 1);
                    break;

                case 'T_GLOBAL_INCLUDE':

                    if ($this->getMaxIncludeDepth() < 1) {
                        throw new IncludeDisabledException("Includes is not allowed!");
                    }

                    $generatedCode .= $this->_execute($this->readGlobalInclude(\trim($tokenValue)), $defines, $currentIncludeDepth + 1);
                    break;

                case 'T_IF':

                    $expressionLevel += 1;

                    if (!$this->executeExpression($tokenValue)) {
                        $skipUntil = ['T_ELSEIF', 'T_ELSE', 'T_ENDIF'];
                    }

                    break;

                case 'T_ELSEIF':

                    if ($expressionLevel == 0) {
                        throw new \LogicException("Expression start token missing!");
                    }

                    if (!$this->executeExpression($tokenValue)) {
                        $skipUntil = ['T_ELSEIF', 'T_ELSE', 'T_ENDIF'];
                        break;
                    }

                    break;

                case 'T_ELSE':

                    if ($expressionLevel == 0) {
                        throw new ExpressionTokenException("Expression start token missing!");
                    }

                    break;

                case 'T_ENDIF':

                    if ($expressionLevel == 0) {
                        throw new ExpressionTokenException("Expression start token missing!");
                    }

                    $expressionLevel -= 1;

                    break;

            }

        }

        if ($expressionLevel > 0) {
            throw new ExpressionTokenException("Expression end token missing!");
        }

        //$generatedCode = \trim($generatedCode);

        if ($this->isMinifyEnabled()) {
            $generatedCode = \preg_replace('/\n+/ium', "\n", $generatedCode);
        }

        return $generatedCode;
    }

    /**
     * @inheritDoc
     */
    public function executeExpression(string $expression): bool
    {
        try {

            return $this->expressionProcessor->execute($expression);

        } catch (NotEnoughTokensException|UnexpectedTokenException|UnexpectedLexemeException $exception) {

            if ($this->isErrorsSkipEnabled()) {
                // ignore exception
                return false;
            }

            throw new ExpressionTokenException($exception->getMessage(), $exception->getCode(), $exception);

        }
    }

    /**
     * @param string $message
     *
     * @return void
     */
    protected function print(string $message): void
    {
        //echo $message . PHP_EOL;
    }

    /**
     * @return int
     */
    protected function getMaxIncludeDepth(): int
    {
        return 0xFF;
    }
}
