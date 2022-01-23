<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Serafim\FFILoader\Tests\Preprocessor;

use Serafim\FFILoader\Preprocessor\AbstractPreprocessor;

/**
 * Class TestPreprocessor
 */
class TestPreprocessor extends AbstractPreprocessor
{
    /** @var string */
    private string $cwd;

    /** @var string[] */
    private array $printOutput;

    //

    /**
     * TestPreprocessor constructor.
     */
    public function __construct(string $cwd)
    {
        parent::__construct();

        $this->cwd = $cwd;
        $this->printOutput = [];
    }

    /**
     * @inheritDoc
     */
    public function isCommentsPreserved(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isErrorsSkipEnabled(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    protected function readGlobalInclude(string $path): string
    {
        $fullPath = $this->cwd . DIRECTORY_SEPARATOR . $path;

        if (!\is_file($fullPath) || !\is_readable($fullPath)) {
            throw new \LogicException("Can't access file {$fullPath}");
        }

        return \file_get_contents($fullPath);
    }

    /**
     * @inheritDoc
     */
    protected function readLocalInclude(string $path): string
    {
        return $this->readGlobalInclude($path);
    }

    /**
     * @inheritDoc
     */
    public function isMinifyEnabled(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    protected function print(string $message): void
    {
        $this->printOutput[] = $message;
        //\fputs(STDERR, $message . PHP_EOL);
    }

    /**
     * @return string[]
     */
    public function getPrintOutput(): array
    {
        return $this->printOutput;
    }
}
