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

use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

class ExternalLink
{
    /**
     * @var int
     */
    protected $pid;

    /**
     * @var int
     */
    protected $uid;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $field;

    /**
     * @var string
     */
    protected $fieldContent;

    /**
     * @var string
     */
    protected $convertedContent = '';

    /**
     * Used for output in dry runs
     *
     * @var array
     */
    protected $conversionMemory = [];

    /**
     * @var Site
     */
    protected $site;

    /**
     * @var SiteLanguage
     */
    protected $language;

    /**
     * @var array
     */
    protected $matches = [];

    /**
     * @var string
     */
    protected $baseUrl;

    public function __construct(int $pid, int $uid, string $table, string $field, string $fieldContent, string $baseUrl, SiteLanguage $language, Site $site)
    {
        $this->pid = $pid;
        $this->uid = $uid;
        $this->table = $table;
        $this->field = $field;
        $this->fieldContent = $fieldContent;
        $this->site = $site;
        $this->language = $language;
        $this->baseUrl = $baseUrl;
        $this->prepareMatchedLinks();
    }

    public function toCliTableRow(): array
    {
        if ($this->conversionMemory === []) {
            return [];
        }
        return [
            $this->pid,
            $this->table,
            $this->uid,
            $this->field,
            implode(', ', array_keys($this->conversionMemory)),
            implode(' ,', $this->conversionMemory)
        ];
    }

    public function prepareMatchedLinks(): void
    {
        $matches = [];
        preg_match('#' . $this->baseUrl . '/?[^" ]*#', $this->fieldContent, $matches);
        $this->matches = $matches;
    }

    public function getMatchedLinks(): array
    {
        return $this->matches;
    }

    public function convert(string $old, string $new): void
    {
        $this->conversionMemory[$old] = $new;
        $this->convertedContent = str_replace($old, $new, $this->hasBeenConverted() ? $this->convertedContent : $this->fieldContent);
    }

    public function hasBeenConverted(): bool
    {
        return $this->convertedContent !== '';
    }

    public function getSite(): Site
    {
        return $this->site;
    }

    public function getLanguage(): SiteLanguage
    {
        return $this->language;
    }

    public function getExtension(): string
    {
        $path = (string)parse_url($this->matches[0], PHP_URL_PATH);
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getUid(): int
    {
        return $this->uid;
    }

    public function getConvertedContent(): string
    {
        return $this->convertedContent;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}
