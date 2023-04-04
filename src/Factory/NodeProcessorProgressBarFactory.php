<?php
declare(strict_types=1);

namespace Netlogix\XmlProcessor\Factory;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NodeProcessorProgressBarFactory
{
    public function createProgressBar(OutputInterface $output): ?ProgressBar
    {
        if (!($output instanceof ConsoleOutputInterface)) {
            return NULL;
        }
        $progressBar = new ProgressBar($output);
        $progressBar->setBarCharacter('<fg=green>⚬</>');
        $progressBar->setEmptyBarCharacter("<fg=red>⚬</>");
        $progressBar->setProgressCharacter("<fg=green>➤</>");
        $progressBar->setFormat(
            "<comment>%node%</comment>\n%current% [%bar%]\n %memory:6s%\n"
        );

        return $progressBar;
    }
}