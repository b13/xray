<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 CMS-extension xray by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

namespace B13\Xray\ExternalLinks\Persistence;

use B13\Xray\ExternalLinks\ExternalLinkCollection;
use TYPO3\CMS\Core\Database\ConnectionPool;

class CollectionWriter
{
    /**
     * @var ConnectionPool
     */
    protected $connectionPool;

    public function __construct(ConnectionPool $connectionPool)
    {
        $this->connectionPool = $connectionPool;
    }

    public function toDatabase(ExternalLinkCollection $collection): void
    {
        foreach ($collection as $link) {
            if (!$link->hasBeenConverted()) {
                continue;
            }

            $connection = $this->connectionPool->getConnectionForTable($link->getTable());
            $connection->update(
                $link->getTable(),
                [
                    $link->getField() => $link->getConvertedContent(),
                ],
                [
                    'uid' => $link->getUid(),
                ],
                [
                    \PDO::PARAM_STR,
                    \PDO::PARAM_INT,
                ]
            );
        }
    }
}
