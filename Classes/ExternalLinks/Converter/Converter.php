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

abstract class Converter
{
    abstract protected function canConvert(ExternalLink $link): bool;
    abstract public function convert(ExternalLink $link): void;
}
