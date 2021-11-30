<?php

declare(strict_types=1);

namespace B13\Xray\Command;

use B13\Xray\ExternalLinks\Configuration\DefaultConfiguration;
use B13\Xray\ExternalLinks\ExternalLinkCollectionFactory;
use B13\Xray\ExternalLinks\Output\TableOutput;
use B13\Xray\ExternalLinks\Persistence\CollectionWriter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExternalLinksCommand extends Command
{
    /**
     * @var ExternalLinkCollectionFactory
     */
    protected $factory;

    /**
     * @var CollectionWriter
     */
    protected $collectionWriter;

    public function __construct(ExternalLinkCollectionFactory $externalLinkCollectionFactory, CollectionWriter $collectionWriter, string $name = null)
    {
        $this->factory = $externalLinkCollectionFactory;
        $this->collectionWriter = $collectionWriter;
        parent::__construct($name);
    }

    public function configure(): void
    {
        $this
            ->setHelp('Finds all external links that could be internal links and migrates them. Use --dry-run to skip migration and only output the findings.')
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'If this option is set, the links will not actually be migrated, but just listed'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $dryRun = $input->hasOption('dry-run') && $input->getOption('dry-run');

        $collection = $this->factory->fromConfiguration(new DefaultConfiguration());
        $collection->convertAll();

        if (!$dryRun) {
            $this->collectionWriter->toDatabase($collection);
        }

        return (new TableOutput($collection, $output, $dryRun))();
    }
}
