<?php

declare(strict_types=1);

namespace B13\Xray\Tests\Unit\ExternalLinks;

use B13\Xray\ExternalLinks\ExternalLink;
use B13\Xray\Tests\Unit\AbstractUnitTest;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ExternalLinkTest extends UnitTestCase
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

    public function prepareMatchedLinksSetsMatchesCorrectlyDataProvider(): \Generator
    {
        yield 'Matching URL in text' => [
            'Content https://example.com/abc hello',
            [
                'https://example.com/abc'
            ],
        ];

        yield 'Not matching URL in text' => [
            'Content https://someotherdomainwhichdoesnotmatch.com/abc hello',
            [],
        ];

        yield 'Matching Link in text' => [
            'Content <a href="https://example.com/abc">abc</a> hello',
            [
                'https://example.com/abc'
            ],
        ];

        yield 'Not matching Link in text' => [
            'Content <a href="https://no.match.example.com/abc">abc</a> hello',
            [],
        ];

        yield 'Matching Link and URL anchor text (anchor text should not match)' => [
            'Content <a href="https://example.com/abc">https://example.com/cef</a> hello',
            [
                'https://example.com/abc'
            ],
        ];

        /*
        // @todo This will fail: URL in anchor text should not match and matches trailing </a>
        yield 'Matching URL only in anchor text (should not match)' => [
            'Content <a href="https://no.match.example.com/path">https://example.com/abc</a>, hello',
            [],
        ];
        */

        /*
        // @todo This will fail: the extracted URL is with trailing comma
        yield 'Matching URL with comma' => [
            'Content https://example.com/abc, hello',
            [
                'https://example.com/abc'
            ],
        ];
        */
    }

    /**
     * @test
     * @dataProvider prepareMatchedLinksSetsMatchesCorrectlyDataProvider
     */
    public function prepareMatchedLinksSetsMatchesCorrectly(string $fieldContent, array $expectedMatches): void
    {
        $subject = $this->instantiateExternalLinkSubject($fieldContent);
        $subject->prepareMatchedLinks();
        $this->assertEquals($expectedMatches, $subject->getMatchedLinks());
    }

    protected function instantiateExternalLinkSubject(string $fieldContent): ExternalLink
    {
        $baseUrl = 'https://example.com';
        $site = new Site('simple-page', 1, [
            'base' => $baseUrl,
            'languages' => [
                [
                    'languageId' => 0,
                    'title' => 'EN',
                    'locale' => 'en_US.UTF-8',
                ],
            ],
        ]);
        return new ExternalLink(1, 1, 'tt_content', 'bodytext', $fieldContent,
            $baseUrl, $site->getDefaultLanguage(), $site
        );
    }

}
