<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 CMS-extension xray by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

namespace B13\Xray\ExternalLinks\Converter;

use B13\Xray\ExternalLinks\ExternalLink;
use TYPO3\CMS\Core\Context\LanguageAspectFactory;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\QueryGenerator;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PageLinkConverter extends Converter implements SingletonInterface
{
    /**
     * @var array
     */
    protected $urlCache = [];

    /**
     * @var PageRepository
     */
    protected $pageRepository;

    /**
     * @var QueryGenerator
     */
    protected $queryGenerator;

    public function __construct(PageRepository $pageRepository, QueryGenerator $queryGenerator)
    {
        $this->pageRepository = $pageRepository;
        $this->queryGenerator = $queryGenerator;
    }

    protected function canConvert(ExternalLink $link): bool
    {
        return !$link->isFile();
    }

    public function convert(ExternalLink $link): void
    {
        if (!$this->canConvert($link)) {
            return;
        }

        foreach ($link->getMatchedLinks() as $matchedLink) {
            foreach ($this->getUrlCandidates($link) as $url) {
                if ($matchedLink === $url['uri']) {
                    $targetUri = 't3://page?uid=' . $url['target'];
                    if ($link->getLanguage()->getLanguageId() !== 0) {
                        $targetUri .= '&_language=' . $link->getLanguage()->getLanguageId();
                    }
                    $link->convert($matchedLink, $targetUri);
                    continue 2;
                }
            }
        }
    }

    private function getUrlCandidates(ExternalLink $link): array
    {
        $urls = [];
        $rootPageId = $link->getSite()->getRootPageId();
        $languageId = $link->getLanguage()->getLanguageId();

        if (isset($this->urlCache[$rootPageId][$languageId])) {
            return $this->urlCache[$rootPageId][$languageId];
        }
        if (!isset($this->urlCache[$rootPageId]) || !is_array($this->urlCache[$rootPageId])) {
            $this->urlCache[$rootPageId] = [];
        }

        // Fetch all pages of the root page recursively
        $pageIds = GeneralUtility::intExplode(',', $this->queryGenerator->getTreeList($rootPageId, 99));
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('pages');
        $pages = $queryBuilder
            ->select('*')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->in('uid', $pageIds)
            )
            ->executeQuery()
            ->fetchAllAssociative();

        $pages = $this->pageRepository->getPagesOverlay(
            $pages,
            $languageId
        );
        $languageAspect = LanguageAspectFactory::createFromSiteLanguage($link->getLanguage());
        foreach ($pages as $page) {
            if (!$this->pageRepository->isPageSuitableForLanguage($page, $languageAspect)) {
                continue;
            }

            $urls[] = [
                'uri' => (string)$link->getSite()->getRouter()->generateUri((string)$page['uid'], ['_language' => $languageId]),
                'target' => $page['uid'],
            ];
        }

        $this->urlCache[$rootPageId][$languageId] = $urls;
        return $urls;
    }
}
