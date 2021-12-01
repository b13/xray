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

use B13\Xray\ExternalLinks\Configuration\ConfigurationInterface;
use B13\Xray\ExternalLinks\Converter\FileLinkConverter;
use B13\Xray\ExternalLinks\Converter\PageLinkConverter;
use Doctrine\DBAL\Driver\Statement;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Site\SiteFinder;

class ExternalLinkCollectionFactory
{
    /**
     * @var ConnectionPool
     */
    protected $connectionPool;

    /**
     * @var PageLinkConverter
     */
    protected $pageLinkConverter;

    /**
     * @var SiteFinder
     */
    protected $siteFinder;

    /**
     * @var FileLinkConverter
     */
    protected $fileLinkConverter;

    public function __construct(
        ConnectionPool $connectionPool,
        SiteFinder $siteFinder,
        PageLinkConverter $pageLinkConverter,
        FileLinkConverter $fileLinkConverter
    ) {
        $this->connectionPool = $connectionPool;
        $this->pageLinkConverter = $pageLinkConverter;
        $this->siteFinder = $siteFinder;
        $this->fileLinkConverter = $fileLinkConverter;
    }

    public function fromConfiguration(ConfigurationInterface $configuration): ExternalLinkCollection
    {
        $collection = new ExternalLinkCollection(
            [
                $this->pageLinkConverter,
                $this->fileLinkConverter,
            ]
        );

        foreach ($configuration->getTablesAndFields() as $table => $fields) {
            foreach ($fields as $field) {
                foreach ($this->siteFinder->getAllSites() as $site) {
                    $siteBase = (string)$site->getBase();
                    $siteBaseIsLanguageBase = false;
                    foreach ($site->getLanguages() as $language) {
                        $languageBase = (string)$language->getBase();
                        if ($siteBase === $languageBase) {
                            $siteBaseIsLanguageBase = true;
                        }
                        $this->addExternalLinksWithBaseUrlToCollection(
                            $collection,
                            $languageBase,
                            $field,
                            $table,
                            $site,
                            $language
                        );
                    }
                    if ($siteBaseIsLanguageBase === false) {
                        $this->addExternalLinksWithBaseUrlToCollection(
                            $collection,
                            $siteBase,
                            $field,
                            $table,
                            $site,
                            $site->getDefaultLanguage()
                        );
                    }
                }
            }
        }
        return $collection;
    }

    private function addExternalLinksWithBaseUrlToCollection(
        ExternalLinkCollection $collection,
        string $baseUrl,
        string $field,
        string $table,
        Site $site,
        SiteLanguage $language
    ): ExternalLinkCollection {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $queryBuilder->select($field, 'pid', 'uid')->from($table);
        $queryBuilder->orWhere(
            $queryBuilder->expr()->like(
                $field,
                $queryBuilder->createNamedParameter(
                    '%' . $queryBuilder->escapeLikeWildcards($baseUrl) . '%'
                )
            )
        );
        $statement = $queryBuilder->execute();
        if (!$statement instanceof Statement) {
            return $collection;
        }
        foreach ($statement as $row) {
            $collection->add(
                new ExternalLink(
                    $row['pid'],
                    $row['uid'],
                    $table,
                    $field,
                    (string)$row[$field],
                    $baseUrl,
                    $language,
                    $site
                )
            );
        }
        return $collection;
    }
}
