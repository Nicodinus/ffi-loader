<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader\Tests\Preprocessor\ExpressionProcessor;

use Serafim\FFILoader\Preprocessor\ExpressionProcessor\Exception\NotEnoughTokensException;
use Serafim\FFILoader\Preprocessor\ExpressionProcessor\Exception\UnexpectedTokenException;
use Serafim\FFILoader\Preprocessor\ExpressionProcessor\ExpressionProcessor;
use Serafim\FFILoader\Preprocessor\Lexer\Exception\UnexpectedLexemeException;
use Serafim\FFILoader\Tests\TestCase;

/**
 * Class ExpressionProcessorTestCase
 */
class ExpressionProcessorTestCase extends TestCase
{
    /**
     * @return void
     */
    public function testExpressionProcessor(): void
    {
        $expressionProcessor = new ExpressionProcessor();
        $operators = ['>', '<', '>=', '<=', '==', '!='];

        $nums = [1,0,-1,10,-10,1.0,-1.0,10.1,-10.1,"true","false"];
        foreach ($nums as $num1) {
            foreach ($nums as $num2) {
                foreach ($operators as $operator) {
                    $evalCode = "return {$num1} {$operator} {$num2};";
                    $expressionCode = "{$num1} {$operator} {$num2}";

                    $evalResult = eval($evalCode);
                    $expressionProcessorResult = $expressionProcessor->execute($expressionCode);

                    $this->assertSame($evalResult, $expressionProcessorResult);
                }
            }
        }

        $versions = ["1.0.0", "1.0.0-dev", "1.0.0-beta1", "0.0.1-alpha-1"];
        foreach ($versions as $version1) {
            foreach ($versions as $version2) {
                foreach ($operators as $operator) {
                    $expressionCode = "{$version1} {$operator} {$version2}";

                    $versionCompareResult = \version_compare($version1, $version2, $operator);
                    $expressionProcessorResult = $expressionProcessor->execute($expressionCode);

                    $this->assertSame($versionCompareResult, $expressionProcessorResult);
                }
            }
        }
    }

    /**
     * @return void
     */
    public function testNotEnoughTokensException(): void
    {
        $this->expectException(NotEnoughTokensException::class);

        $expressionProcessor = new ExpressionProcessor();

        $expressionProcessor->execute("1 > ");
    }

    /**
     * @return void
     */
    public function testUnexpectedTokenException(): void
    {
        $this->expectException(UnexpectedTokenException::class);

        $expressionProcessor = new ExpressionProcessor();

        $expressionProcessor->execute("1 > 10 0");
    }

    /**
     * @return void
     */
    public function testUnexpectedLexemeException(): void
    {
        $this->expectException(UnexpectedLexemeException::class);

        $expressionProcessor = new ExpressionProcessor();

        $expressionProcessor->execute("asd");
    }
}
