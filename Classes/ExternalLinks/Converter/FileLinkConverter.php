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
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\StorageRepository;

class FileLinkConverter extends Converter
{
    /**
     * @var StorageRepository
     */
    protected $storageRepository;

    /**
     * @var array
     */
    protected $storageCache = [];

    /**
     * @var ResourceFactory
     */
    protected $resourceFactory;

    public function __construct(StorageRepository $storageRepository, ResourceFactory $resourceFactory)
    {
        $this->storageRepository = $storageRepository;
        $this->resourceFactory = $resourceFactory;
    }

    protected function canConvert(ExternalLink $link): bool
    {
        return in_array(
            $link->getExtension(),
            explode(',', $GLOBALS['TYPO3_CONF_VARS']['SYS']['mediafile_ext'] ?? '')
        );
    }

    public function convert(ExternalLink $link): void
    {
        foreach ($link->getMatchedLinks() as $matchedLink) {
            $fileId = $this->finMatchingFileId($matchedLink, $link);
            if ($fileId === 0) {
                continue;
            }
            $link->convert($matchedLink, 't3://file?uid=' . $fileId);
        }
    }

    private function finMatchingFileId(string $uri, ExternalLink $link): int
    {
        if (empty($this->storageCache)) {
            $this->fetchStorages();
        }

        foreach ($this->storageCache as $storageId => $storagePath) {
            $storageUrl = trim($link->getBaseUrl(), '/') . '/' . ltrim($storagePath, '/');
            // Does our URI start with storage path?
            if (strpos($uri, $storageUrl, 0) === 0) {
                $fileIdentifier = '/' . substr($uri, strlen($storageUrl));
                $file = $this->resourceFactory->getFileObjectByStorageAndIdentifier($storageId, $fileIdentifier);
                if (!$file instanceof File) {
                    return 0;
                }
                return $file->getUid();
            }
        }
        return 0;
    }

    private function fetchStorages(): void
    {
        $storages = $this->storageRepository->findAll();
        foreach ($storages as $storage) {
            if (!$storage->isOnline()) {
                continue;
            }
            $this->storageCache[$storage->getUid()] = $storage->getRootLevelFolder()->getPublicUrl();
        }
    }
}
