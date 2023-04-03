<?php

namespace Netlogix\XmlProcessor\Tests\Unit;

use Netlogix\XmlProcessor\NodeProcessor\CloseNodeProcessorInterface;
use Netlogix\XmlProcessor\NodeProcessor\Context\CloseContext;
use Netlogix\XmlProcessor\NodeProcessor\Context\OpenContext;
use Netlogix\XmlProcessor\NodeProcessor\Context\TextContext;
use Netlogix\XmlProcessor\NodeProcessor\NodeProcessorInterface;
use Netlogix\XmlProcessor\NodeProcessor\OpenNodeProcessorInterface;
use Netlogix\XmlProcessor\NodeProcessor\TextNodeProcessorInterface;
use Netlogix\XmlProcessor\XmlProcessor;
use Netlogix\XmlProcessor\XmlProcessorContext;
use PHPUnit\Framework\TestCase;


class XmlProcessorTest extends TestCase
{
    public function test__construct()
    {
        $xmlProcessor = new XmlProcessor([
            $this->getMockForAbstractClass(NodeProcessorInterface::class)
        ]);
        self::assertInstanceOf(XmlProcessor::class, $xmlProcessor);
    }

    public function testGetProcessorContext(): void
    {
        $xmlProcessor = new XmlProcessor([
            $this->getMockForAbstractClass(NodeProcessorInterface::class)
        ]);
        $context = $xmlProcessor->getProcessorContext();
        self::assertInstanceOf(XmlProcessorContext::class, $context);
    }

    public function testProcessFile()
    {
        $nodeProcessor = $this->getMockForAbstractClass(NodeProcessorInterface::class);

        $openCallableMock = $this->getMockBuilder(OpenNodeProcessorInterface::class)->getMock();
        $openCallableMock->expects($this->atLeastOnce())->method('openElement')->with($this->isInstanceOf(OpenContext::class));
        $textCallableMock = $this->getMockBuilder(TextNodeProcessorInterface::class)->getMock();
        $textCallableMock->expects($this->atLeastOnce())->method('textElement')->with($this->isInstanceOf(TextContext::class));
        $closeCallableMock = $this->getMockBuilder(CloseNodeProcessorInterface::class)->getMock();
        $closeCallableMock->expects($this->atLeastOnce())->method('closeElement')->with($this->isInstanceOf(CloseContext::class));

        $nodeProcessor->method('getSubscribedEvents')
            ->will(
                $this->returnCallback(fn() => yield from [
                    'NodeType_' . \XMLReader::ELEMENT => [$openCallableMock, 'openElement'],
                    'NodeType_' . \XMLReader::END_ELEMENT => [$closeCallableMock, 'closeElement'],
                    'NodeType_' . \XMLReader::TEXT => [$textCallableMock, 'textElement']
                ])
            );

        $xmlProcessor = new XmlProcessor([
            $nodeProcessor
        ]);

        $xmlProcessor->processFile(__DIR__ . '/../Fixtures/XmlProcessorTest/test.xml');
    }
}
