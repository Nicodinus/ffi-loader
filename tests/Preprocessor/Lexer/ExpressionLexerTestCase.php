<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nicodinus\FFILoader\Tests\Preprocessor\Lexer;

use Nicodinus\FFILoader\Preprocessor\Lexer\Exception\UnexpectedLexemeException;
use Nicodinus\FFILoader\Preprocessor\Lexer\ExpressionLexer;
use Nicodinus\FFILoader\Tests\TestCase;

/**
 * Class ExpressionLexerTestCase
 */
class ExpressionLexerTestCase extends TestCase
{
    /**
     * @return void
     */
    public function testExpressionLexer(): void
    {
        $sourceCode = <<<'SOURCE_CODE'
1.0.0
10.10.10
0.0.1-dev
1.0
-1.0
1
-1
true
false
>=
>
<=
<
==
!=
SOURCE_CODE;
        $sourceCodeLexemes = [
            ['T_VERSION' => '1.0.0'],
            ['T_VERSION' => '10.10.10'],
            ['T_VERSION' => '0.0.1-dev'],
            ['T_VERSION' => '1.0'],
            ['T_DIGIT' => '-1.0'],
            ['T_DIGIT' => '1'],
            ['T_DIGIT' => '-1'],
            ['T_BOOL' => 'true'],
            ['T_BOOL' => 'false'],
            'T_GTE',
            'T_GT',
            'T_LTE',
            'T_LT',
            'T_EQ',
            'T_NEQ',
        ];

        $lexer = new ExpressionLexer();

        $index = 0;
        foreach ($lexer->lex($sourceCode, 0) as $token) {

            $currentExpectedLexeme = $sourceCodeLexemes[$index];
            $currentExpectedLexemeName = null;
            $currentExpectedLexemeValue = null;

            if (\is_array($currentExpectedLexeme)) {
                $currentExpectedLexemeName = \key($currentExpectedLexeme);
                $currentExpectedLexemeValue = $currentExpectedLexeme[$currentExpectedLexemeName];
            } else {
                $currentExpectedLexemeName = $currentExpectedLexeme;
            }

            $this->assertSame($currentExpectedLexemeName, $token->getName());
            if ($currentExpectedLexemeValue !== null) {
                $this->assertSame($currentExpectedLexemeValue, $token->getValue());
            }

            $index += 1;
        }
    }

    /**
     * @return void
     */
    public function testUnexpectedLexemeException(): void
    {
        $this->expectException(UnexpectedLexemeException::class);

        $lexer = new ExpressionLexer();

        foreach ($lexer->lex("a", 0) as $token) {}
    }
}
