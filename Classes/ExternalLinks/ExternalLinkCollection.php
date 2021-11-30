<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 CMS-extension xray by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

namespace B13\Xray\ExternalLinks;

use B13\Xray\ExternalLinks\Converter\Converter;

/**
 * @implements \Iterator<ExternalLink>
 */
class ExternalLinkCollection implements \Iterator
{
    /**
     * @var Converter[]
     */
    protected $converter;

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var ExternalLink[]
     */
    private $links = [];

    public function __construct(array $converter)
    {
        $this->converter = $converter;
    }

    public function add(ExternalLink $link): void
    {
        $this->links[] = $link;
    }

    public function current(): ExternalLink
    {
        return $this->links[$this->position];
    }

    public function next(): void
    {
        $this->position++;
    }

    public function key(): int
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return isset($this->links[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function convertAll(): void
    {
        foreach ($this->links as $link) {
            foreach ($this->converter as $converter) {
                if ($link->hasBeenConverted()) {
                    continue 2;
                }
                $converter->convert($link);
            }
        }
    }

    public function isEmpty(): bool
    {
        return count($this->links) === 0;
    }
}
