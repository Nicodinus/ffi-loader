<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nicodinus\FFILoader\Tests\Preprocessor\Lexer;

use Nicodinus\FFILoader\Preprocessor\Lexer\Lexer;
use Nicodinus\FFILoader\Tests\TestCase;

/**
 * Class LexerTestCase
 */
class LexerTestCase extends TestCase
{
    /**
     * @return void
     */
    public function testLexer(): void
    {
        $sourceCode = <<<'SOURCE_CODE'
/*
GROUP COMMENT
ignore code here
#include "LOL"
#ifdef LOL
*/
// COMMENT #include "LOL" #ifdef LOL
#include "local_include_file"
#include <global_include_file>
#define TEST_DEFINE 1337 VALUE
#undef TEST_DEFINE
#ifdef TEST_DEFINE
#ifndef TEST_DEFINE
#endif
#elseif 42 < 0
#else
#if 42 > 0
SOURCE CODE HERE
SOURCE_CODE;
        $sourceCodeLexemes = [
            'T_GROUP_COMMENT',
            'T_NEW_LINE',
            'T_COMMENT',
            'T_NEW_LINE',
            ['T_LOCAL_INCLUDE' => 'local_include_file'],
            'T_NEW_LINE',
            ['T_GLOBAL_INCLUDE' => 'global_include_file'],
            'T_NEW_LINE',
            ['T_DEFINE' => 'TEST_DEFINE 1337 VALUE'],
            'T_NEW_LINE',
            ['T_UNDEF' => 'TEST_DEFINE'],
            'T_NEW_LINE',
            ['T_IFDEF' => 'TEST_DEFINE'],
            'T_NEW_LINE',
            ['T_IFNDEF' => 'TEST_DEFINE'],
            'T_NEW_LINE',
            'T_ENDIF',
            'T_NEW_LINE',
            ['T_ELSEIF' => '42 < 0'],
            'T_NEW_LINE',
            'T_ELSE',
            'T_NEW_LINE',
            ['T_IF' => '42 > 0'],
            'T_NEW_LINE',
            ['T_SOURCE' => 'SOURCE CODE HERE'],
        ];

        $lexer = new Lexer();

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
}
