<?php
declare(strict_types=1);

namespace Netlogix\XmlProcessor\NodeProcessor;


use Netlogix\XmlProcessor\Factory\NodeProcessorProgressBarFactory;
use Netlogix\XmlProcessor\NodeProcessor\Context\OpenContext;
use Netlogix\XmlProcessor\XmlProcessor;
use Netlogix\XmlProcessor\XmlProcessorContext;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProgressOutputNodeProcessor implements NodeProcessorInterface, OpenNodeProcessorInterface
{
    protected ?OutputInterface $output = NULL;

    protected ?ProgressBar $progressBar = NULL;

    protected NodeProcessorProgressBarFactory $progressBarFactory;

    function __construct(?NodeProcessorProgressBarFactory $progressBarFactory = NULL)
    {
        $this->progressBarFactory = $progressBarFactory ?? new NodeProcessorProgressBarFactory();
    }

    public function SetOutput(?ConsoleOutputInterface $output = NULL): void
    {
        $this->output = $output;
        $this->progressBar = NULL;
    }

    function getSubscribedEvents(string $nodePath, XmlProcessorContext $context): \Iterator
    {
        if ($this->progressBar === NULL) {
            yield XmlProcessor::EVENT_OPEN_FILE => [$this, 'openFile'];
        } else {
            yield 'NodeType_' . \XMLReader::ELEMENT => [$this, 'openElement'];
            yield XmlProcessor::EVENT_END_OF_FILE => [$this, 'endOfFile'];
        }
    }

    function openFile(): void
    {
        if ($this->progressBar !== NULL) {
            $this->progressBar->finish();
        }
        $this->progressBar = $this->progressBarFactory->createProgressBar($this->output);
    }

    function openElement(OpenContext $context): void
    {
        $this->progressBar->advance();
        $this->progressBar->setMessage($context->getNodePath(), 'node');
    }

    function endOfFile(): void
    {
        $this->progressBar->finish();
        $this->progressBar = NULL;
    }
}
