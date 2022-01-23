<?php

/**
 * This file is part of FFI Loader package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nicodinus\FFILoader\Preprocessor\Lexer;

use Phplrt\Contracts\Lexer\LexerInterface;

/**
 * Class AbstractLexer
 */
abstract class AbstractLexer implements LexerInterface
{
    /** @var string */
    private string $pcre;

    //

    /**
     * AbstractLexer constructor.
     */
    public function __construct()
    {
        if (!$this->isLazyCompileEnabled()) {
            $this->pcre = $this->compile($this->getLexTokens());
        }
    }

    /**
     * @inheritDoc
     */
    public function lex($source, int $offset = 0): iterable
    {
        \assert(\is_string($source), 'Source argument MUST be a string');

        $source = \str_replace("\r", '', $source);

        if (empty($this->pcre)) {
            $this->pcre = $this->compile($this->getLexTokens());
        }

        \preg_match_all($this->pcre, $source, $matches, $this->getPcreFlags(), $offset);

        foreach ($matches as $match) {
            $name = \array_pop($match);
            $offset = $match[0][1];
            $value = $match[\array_key_last($match)][0];

            yield new Token($name, $value, $offset);
        }
    }

    /**
     * @param iterable $tokens
     *
     * @return string
     */
    protected function compile(iterable $tokens): string
    {
        $groups = [];

        foreach ($tokens as $name => $pcre) {
            $groups[] = "(?:(?:$pcre)(*MARK:$name))";
        }

        return \vsprintf('/\\G(?|%s)/Ssum', [
            \implode('|', $groups),
        ]);
    }

    /**
     * @return int
     */
    protected function getPcreFlags(): int
    {
        return \PREG_SET_ORDER | \PREG_OFFSET_CAPTURE;
    }

    /**
     * @return bool
     */
    protected function isLazyCompileEnabled(): bool
    {
        return true;
    }

    //

    /**
     * @return iterable|string[]
     */
    protected abstract function getLexTokens(): iterable;
}
