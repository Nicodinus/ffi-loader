<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nicodinus\FFILoader\Tests\Preprocessor;

use Nicodinus\FFILoader\Tests\TestCase;

/**
 * Class PreprocessorTestCase
 */
class PreprocessorTestCase extends TestCase
{
    /**
     * @return void
     */
    public function testPreprocessor(): void
    {
        $preprocessor = new TestPreprocessor(__DIR__ . DIRECTORY_SEPARATOR . "resources");

        $this->assertSame("\nSOURCE CODE HERE\n", $preprocessor->execute(\file_get_contents(__DIR__ . "/resources/main.h")));

        $printOutputExpected = [
            "Hello from test1.h",
            "Hello from main.h",
            "yoLO 1337",
        ];

        $this->assertSame($printOutputExpected, $preprocessor->getPrintOutput());
    }
}
