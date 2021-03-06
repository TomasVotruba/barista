<?php

declare(strict_types=1);

namespace Barista\Command;

use Barista\Configuration\Option;
use Barista\LatteUpgrader;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Output\ConsoleDiffer;

final class UpgradeCommand extends AbstractBaristaCommand
{
    public function __construct(
        private LatteUpgrader $latteUpgrader,
        private ConsoleDiffer $consoleDiffer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('upgrade');
        $this->setDescription('Upgrade what you can from Latte 2 to 3');
        $this->addOption(Option::DRY_RUN, null, InputOption::VALUE_NONE, 'Do not change file content, just dry run');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $isDryRun = (bool) $input->getOption(Option::DRY_RUN);

        $latteFileInfos = $this->findLatteFileInfos($input);

        $filesNote = sprintf('Processing %d files', count($latteFileInfos));
        $this->symfonyStyle->note($filesNote);

        foreach ($latteFileInfos as $latteFileInfo) {
            $originalFileContents = FileSystem::read($latteFileInfo->getRealPath());
            $changedFileContent = $this->latteUpgrader->upgradeFileContent($originalFileContents);

            // nothing to change
            if ($originalFileContents === $changedFileContent) {
                continue;
            }

            if ($isDryRun) {
                $noteMessage = sprintf('File "%s" would be changed (running in "dry-run" mode):', $latteFileInfo->getRealPath());
                $this->symfonyStyle->note($noteMessage);
                $this->consoleDiffer->diff($originalFileContents, $changedFileContent);
            } else {
                $noteMessage = sprintf('File "%s" was changed', $latteFileInfo->getRealPath());
                $this->symfonyStyle->note($noteMessage);

                FileSystem::write($latteFileInfo->getRealPath(), $changedFileContent);
            }
        }

        return self::SUCCESS;
    }
}
