<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader\Preprocessor\Lexer;

use Serafim\FFILoader\Preprocessor\Lexer\Exception\UnexpectedLexemeException;

/**
 * Class ExpressionLexer
 */
class ExpressionLexer extends AbstractLexer
{
    /**
     * @inheritDoc
     */
    protected function getLexTokens(): iterable
    {
        return [
            // Operands
            'T_VERSION'    => '\d+(?:\.\d+)+(?:[^\s]*)',
            'T_DIGIT'      => '-?(?:\d*\.\d+|\d+)', //'\d+',
            'T_BOOL'       => 'true|false',
            // Operators
            'T_GTE'        => '>=',
            'T_GT'         => '>',
            'T_LTE'        => '<=',
            'T_LT'         => '<',
            'T_EQ'         => '==',
            'T_NEQ'        => '!=',
            // Other
            'T_WHITESPACE' => '\s+',
            'T_UNKNOWN'    => '.+?',
        ];
    }

    /**
     * @inheritDoc
     *
     * @throws UnexpectedLexemeException
     */
    public function lex($source, int $offset = 0): iterable
    {
        foreach (parent::lex($source, $offset) as $token)
        {
            switch ($token->getName())
            {
                case 'T_WHITESPACE':
                    continue 2;
                case 'T_UNKNOWN':
                    throw UnexpectedLexemeException::create($token->getValue());
                default:
                    yield $token;
                    break;
            }
        }
    }
}
