<?php
declare(strict_types=1);

namespace Netlogix\XmlProcessor\Tests\Unit\NodeProcessor;

use Netlogix\XmlProcessor\NodeProcessor\Context\OpenContext;
use Netlogix\XmlProcessor\NodeProcessor\ProgressOutputNodeProcessor;
use Netlogix\XmlProcessor\XmlProcessor;
use Netlogix\XmlProcessor\XmlProcessorContext;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

class ProgressOutputNodeProcessorTest extends TestCase
{
    public function test__construct(): void
    {
        $nodeProcessor = new ProgressOutputNodeProcessor();
        self::assertInstanceOf(ProgressOutputNodeProcessor::class, $nodeProcessor);
    }

    public function testGetSubscribedEvents(): void
    {
        $nodeProcessor = new ProgressOutputNodeProcessor();
        $context = $this->getMockBuilder(XmlProcessorContext::class)
            ->disableOriginalConstructor()
            ->getMock();

        self::assertEquals(
            [
                XmlProcessor::EVENT_OPEN_FILE => [$nodeProcessor, 'openFile']
            ],
            iterator_to_array($nodeProcessor->getSubscribedEvents('test', $context))
        );

        $output = $this::getMockForAbstractClass(ConsoleOutputInterface::class);
        $nodeProcessor->setOutput($output);
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
        $reflectionClass = new \ReflectionClass(ProgressOutputNodeProcessor::class);

        $nodeProcessor = new ProgressOutputNodeProcessor();
        $nodeProcessor->openFile();
        self::assertNull($reflectionClass->getProperty('progressBar')->getValue($nodeProcessor));

        $output = $this::getMockForAbstractClass(ConsoleOutputInterface::class);
        $nodeProcessor->setOutput($output);
        $nodeProcessor->openFile();
        self::assertInstanceOf(ProgressBar::class, $reflectionClass->getProperty('progressBar')->getValue($nodeProcessor));

        self::markTestIncomplete('ToDo: $this->progressBar->finish()');
    }

    public function testOpenElement(): void
    {
        $nodeProcessor = new ProgressOutputNodeProcessor();
        $output = $this::getMockForAbstractClass(ConsoleOutputInterface::class);
        $nodeProcessor->setOutput($output);
        $nodeProcessor->openFile();

        $openContext = $this->createMock(OpenContext::class);
        $openContext->method('getNodePath')->willReturn('foo/bar');

        $nodeProcessor->openElement($openContext);
        $reflectionClass = new \ReflectionClass($nodeProcessor);

        /** @var ProgressBar $progressBar */
        $progressBar = $reflectionClass->getProperty('progressBar')->getValue($nodeProcessor);
        self::assertEquals(1, $progressBar->getProgress());
        self::assertEquals('foo/bar', $progressBar->getMessage('node'));
    }

    function testEndOfFile(): void
    {
        $nodeProcessor = new ProgressOutputNodeProcessor();
        $output = $this::getMockForAbstractClass(ConsoleOutputInterface::class);
        $nodeProcessor->setOutput($output);
        $nodeProcessor->openFile();

        $nodeProcessor->endOfFile();
        $reflectionClass = new \ReflectionClass($nodeProcessor);
        $progressBar = $reflectionClass->getProperty('progressBar');
        self::assertNull($progressBar->getValue($nodeProcessor));
    }
}
