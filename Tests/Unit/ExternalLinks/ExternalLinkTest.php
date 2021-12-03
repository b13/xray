<?php

declare(strict_types=1);

namespace B13\Xray\Tests\Unit\ExternalLinks;

use B13\Xray\ExternalLinks\ExternalLink;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ExternalLinkTest  extends UnitTestCase
{
    /**
     * @var bool Reset singletons created by subject
     */
    protected $resetSingletonInstances = true;

    /**
     * @var ExternalLink
     */
    protected $externalLinkSubject;

    protected function setUp(): void
    {
        parent::setUp();

    }

    protected function setupExternalLinkSubject(string $fieldContent): ExternalLink
    {
        $baseUrl =
        return new ExternalLink(1, int 1, 'tt_content', 'bodytext', $fieldContent, $baseUrl, SiteLanguage $language, Site $site);
    }

}
