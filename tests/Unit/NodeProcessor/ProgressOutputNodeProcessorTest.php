<?php
declare(strict_types=1);

namespace Netlogix\XmlProcessor\Tests\Unit\NodeProcessor;

use Netlogix\XmlProcessor\Factory\NodeProcessorProgressBarFactory;
use Netlogix\XmlProcessor\NodeProcessor\Context\OpenContext;
use Netlogix\XmlProcessor\NodeProcessor\ProgressOutputNodeProcessor;
use Netlogix\XmlProcessor\XmlProcessor;
use Netlogix\XmlProcessor\XmlProcessorContext;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

class ProgressOutputNodeProcessorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $reflectionClass = new \ReflectionClass(ProgressOutputNodeProcessor::class);
        $this->progressBarProperty = $reflectionClass->getProperty('progressBar');
        $this->progressBarProperty->setAccessible(true);
    }

    public function test__construct(): void
    {
        $nodeProcessor = new ProgressOutputNodeProcessor();
        self::assertInstanceOf(ProgressOutputNodeProcessor::class, $nodeProcessor);
    }

    public function testGetSubscribedEvents(): void
    {
        $progressBarFactory = $this::getMockBuilder(NodeProcessorProgressBarFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $progressBarFactory->method('createProgressBar')->willReturn($this->createMock(ProgressBar::class));
        $nodeProcessor = new ProgressOutputNodeProcessor($progressBarFactory);
        $context = $this->getMockBuilder(XmlProcessorContext::class)
            ->disableOriginalConstructor()
            ->getMock();

        self::assertEquals(
            [
                XmlProcessor::EVENT_OPEN_FILE => [$nodeProcessor, 'openFile']
            ],
            iterator_to_array($nodeProcessor->getSubscribedEvents('test', $context))
        );

        $nodeProcessor->setOutput($this->getOutputMock());
        $nodeProcessor->openFile();

        self::assertEquals(
            [
                'NodeType_' . \XMLReader::ELEMENT => [$nodeProcessor, 'openElement'],
                XmlProcessor::EVENT_END_OF_FILE => [$nodeProcessor, 'endOfFile']
            ],
            iterator_to_array($nodeProcessor->getSubscribedEvents('test', $context))
        );
    }

    public function testOpenFile(): void
    {
        $nodeProcessor = new ProgressOutputNodeProcessor();
        $nodeProcessor->openFile();
        self::assertNull($this->progressBarProperty->getValue($nodeProcessor));

        $progressBar = $this->getMockBuilder(ProgressBar::class)->disableOriginalConstructor()
            ->getMock();
        $progressBar->expects($this->once())->method('finish');
        $progressBarFactory = $this::getMockBuilder(NodeProcessorProgressBarFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $progressBarFactory->method('createProgressBar')->willReturn($progressBar);

        $nodeProcessor = new ProgressOutputNodeProcessor($progressBarFactory);
        $nodeProcessor->setOutput($this->getOutputMock());
        $nodeProcessor->openFile();
        self::assertInstanceOf(ProgressBar::class, $this->progressBarProperty->getValue($nodeProcessor));

        self::markTestIncomplete('ToDo: $this->progressBar->finish()');
    }

    public function testOpenElement(): void
    {

        $progressBar = $this->getMockBuilder(ProgressBar::class)->disableOriginalConstructor()
            ->getMock();

        $progressBar->expects($this->once())->method('advance')->with(1);
        $progressBar->expects($this->once())->method('setMessage')->with('foo/bar', 'node');
        $progressBarFactory = $this::getMockBuilder(NodeProcessorProgressBarFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $progressBarFactory->method('createProgressBar')->willReturn($progressBar);

        $nodeProcessor = new ProgressOutputNodeProcessor($progressBarFactory);
        $nodeProcessor->setOutput($this->getOutputMock());
        $nodeProcessor->openFile();

        $openContext = $this->createMock(OpenContext::class);
        $openContext->method('getNodePath')->willReturn('foo/bar');

        $nodeProcessor->openElement($openContext);
    }

    function testEndOfFile(): void
    {
        $progressBar = $this->getMockBuilder(ProgressBar::class)->disableOriginalConstructor()
            ->getMock();
        $progressBar->expects($this->once())->method('finish');
        $progressBarFactory = $this::getMockBuilder(NodeProcessorProgressBarFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $progressBarFactory->method('createProgressBar')->willReturn($progressBar);

        $nodeProcessor = new ProgressOutputNodeProcessor($progressBarFactory);
        $nodeProcessor->setOutput($this->getOutputMock());
        $nodeProcessor->openFile();

        $nodeProcessor->endOfFile();
        $progressBar = $this->progressBarProperty;
        self::assertNull($progressBar->getValue($nodeProcessor));
    }

    private function getOutputMock(): ConsoleOutputInterface
    {
        $output = $this::getMockForAbstractClass(ConsoleOutputInterface::class);
        $output->method('getErrorOutput')->willReturn($output);
        $output->method('isDecorated')->willReturn(true);
        return $output;
    }
}
