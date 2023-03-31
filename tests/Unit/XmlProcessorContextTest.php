<?php

namespace Netlogix\XmlProcessor;

use Netlogix\XmlProcessor\NodeProcessor\NodeProcessorInterface;
use PHPUnit\Framework\TestCase;

class XmlProcessorContextTest extends TestCase
{
    /** @var NodeProcessorInterface */
    static private $processor;

    function setUp(): void
    {
        parent::setUp();
        self::$processor = TestCase::getMockBuilder(NodeProcessorInterface::class)->getMock();
    }

    function testGetXMLReader()
    {
        $xmlReader = $this->getXMLReaderMock();
        $context = new XmlProcessorContext($xmlReader, []);
        $this->assertSame($xmlReader, $context->getXMLReader());
    }

    /**
     * @dataProvider getProcessorDataProvider
     */
    function testGetProcessor($processor, $expected)
    {
        $context = new XmlProcessorContext($this->getXMLReaderMock(), [$processor]);
        $this->assertSame($expected, $context->getProcessor(NodeProcessorInterface::class));
    }

    public static function getProcessorDataProvider(): \Generator
    {
        yield [self::$processor, self::$processor];
        yield [[], NULL];
    }

    private function getXMLReaderMock(): \XMLReader
    {
        return $this->getMockBuilder(\XMLReader::class)->getMock();
    }
}
