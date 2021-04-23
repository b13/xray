<?php
declare(strict_types=1);

/*
 * This file is part of TYPO3 CMS-extension xray by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

namespace B13\Xray\ExternalLinks\Configuration;

class DefaultConfiguration implements ConfigurationInterface
{
    /**
     * @var string[][]
     */
    protected $tablesAndFields = [
        'tt_content' => [
            'header_link',
            'bodytext'
        ]
    ];

    public function getTablesAndFields(): array
    {
        return $this->tablesAndFields;
    }
}
