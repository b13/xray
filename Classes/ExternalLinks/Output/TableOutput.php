<?php
declare(strict_types=1);

/*
 * This file is part of TYPO3 CMS-extension xray by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

namespace B13\Xray\ExternalLinks\Output;

use B13\Xray\ExternalLinks\ExternalLinkCollection;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class TableOutput
{
    /**
     * @var ExternalLinkCollection
     */
    protected $collection;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var bool
     */
    protected $dryRun;

    public function __construct(ExternalLinkCollection $collection, OutputInterface $output, bool $dryRun)
    {
        $this->collection = $collection;
        $this->output = $output;
        $this->dryRun = $dryRun;
    }

    public function __invoke(): int
    {
        $comment = $this->dryRun ? 'DRY RUN. This is what would happen' : 'Migration successful! Here is what has been done';
        $this->output->writeln([
            '',
            '<comment>' . $comment . ': </comment>',
            '',
        ]);

        if ($this->collection->isEmpty()) {
            $this->output->writeln([
                '<info>Nothing.</info>',
                '<info>No external links found that could be converted to internal links.</info>',
                '',
            ]);
            return 0;
        }

        $table = new Table($this->output);
        $table->setHeaders(['PID', 'Table', 'UID', 'Field', 'Found external Link', 'Would be converted to']);
        foreach ($this->collection as $externalLink) {
            $result = $externalLink->toCliTableRow();
            if ($result === []) {
                continue;
            }
            $table->addRow($result);
        }
        $table->render();

        return 0;
    }
}
