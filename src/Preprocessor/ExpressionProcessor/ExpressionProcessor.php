<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader\Preprocessor\ExpressionProcessor;

use Phplrt\Contracts\Lexer\LexerInterface;
use Phplrt\Contracts\Lexer\TokenInterface;
use Serafim\FFILoader\Preprocessor\ExpressionProcessor\Exception\NotEnoughTokensException;
use Serafim\FFILoader\Preprocessor\ExpressionProcessor\Exception\UnexpectedTokenException;
use Serafim\FFILoader\Preprocessor\Lexer\ExpressionLexer;

/**
 * Class ExpressionProcessor
 */
class ExpressionProcessor implements ExpressionProcessorInterface
{
    /** @var LexerInterface */
    private LexerInterface $lexer;

    //

    /**
     * ExpressionProcessor constructor.
     *
     * @param LexerInterface|null $expressionLexer
     */
    public function __construct(LexerInterface $expressionLexer = null)
    {
        $this->lexer = $expressionLexer ?? new ExpressionLexer();
    }

    /**
     * @inheritDoc
     */
    public function execute(string $expression): bool
    {
        /** @var TokenInterface $lvalue */
        $lvalue = null;
        /** @var TokenInterface $rvalue */
        $rvalue = null;
        /** @var TokenInterface $operator */
        $operator = null;

        $index = 0;
        foreach ($this->lexer->lex($expression, 0) as $token)
        {
            switch ($index)
            {
                case 0:
                    $lvalue = $token;
                    break;
                case 1:
                    $operator = $token;
                    break;
                case 2:
                    $rvalue = $token;
                    break;
                default:
                    throw UnexpectedTokenException::create($token);
            }

            $index += 1;
        }

        switch ($index)
        {
            case 1:
                return \strtolower($lvalue->getValue()) !== 'false' && $lvalue->getValue() != 0;
            case 3:
                break;
            default:
                throw new NotEnoughTokensException();
        }

        /*
         * if -1 then lvalue < rvalue
         * if 0 then lvalue = rvalue
         * if 1 then lvalue > rvalue
         */
        if ($lvalue->getName() === 'T_BOOL' || $rvalue->getName() === 'T_BOOL') {
            if ($lvalue->getName() === 'T_BOOL') {
                $lvalueBool = \strtolower($lvalue->getValue()) !== 'false';
            } else {
                $lvalueBool = $lvalue->getValue() != 0;
            }
            if ($rvalue->getName() === 'T_BOOL') {
                $rvalueBool = \strtolower($rvalue->getValue()) !== 'false';
            } else {
                $rvalueBool = $rvalue->getValue() != 0;
            }
            if ($lvalueBool && !$rvalueBool) {
                $compareResult = 1;
            } elseif (!$lvalueBool && $rvalueBool) {
                $compareResult = -1;
            } else {
                $compareResult = 0;
            }
        } else if ($lvalue->getName() === 'T_VERSION' || $rvalue->getName() === 'T_VERSION') {
            $compareResult = \version_compare($lvalue->getValue(), $rvalue->getValue());
        } else {
            $compareResult = $lvalue->getValue() < $rvalue->getValue() ? -1 : ($lvalue->getValue() > $rvalue->getValue() ? 1 : 0);
        }

        switch ($compareResult)
        {
            case -1:
                switch ($operator->getName())
                {
                    case "T_LTE":
                    case "T_LT":
                    case "T_NEQ":
                        return true;
                    default:
                        return false;
                }
            case 0:
                switch ($operator->getName())
                {
                    case "T_LTE":
                    case "T_GTE":
                    case "T_EQ":
                        return true;
                    default:
                        return false;
                }
            case 1:
                switch ($operator->getName())
                {
                    case "T_GTE":
                    case "T_GT":
                    case "T_NEQ":
                        return true;
                    default:
                        return false;
                }
        }
    }
}
