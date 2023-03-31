<?php

namespace Netlogix\XmlProcessor;

use Netlogix\XmlProcessor\NodeProcessor\NodeProcessorInterface;
use PHPUnit\Framework\TestCase;

class XmlProcessorContextTest extends TestCase
{
    function testGetXMLReader()
    {
        $xmlReader = $this->getMockBuilder(\XMLReader::class)->getMock();
        $context = new XmlProcessorContext($xmlReader, []);
        $this->assertSame($xmlReader, $context->getXMLReader());
    }

    /**
     * @dataProvider getProcessorDataProvider
     */
    function testGetProcessor($processor, $expected)
    {
        $xmlReader = $this->getMockBuilder(\XMLReader::class)->getMock();
        $context = new XmlProcessorContext($xmlReader, [$processor]);
        $this->assertSame($expected, $context->getProcessor(NodeProcessorInterface::class));
    }

    function getProcessorDataProvider(): \Generator
    {
        $processor = $this->getMockBuilder(NodeProcessorInterface::class)->getMock();
        yield [$processor, $processor];
        yield [[], NULL];

    }
}
