<?php
declare(strict_types=1);

namespace Netlogix\XmlProcessor\Tests\Unit;

use Netlogix\XmlProcessor\NodeProcessor\CloseNodeProcessorInterface;
use Netlogix\XmlProcessor\NodeProcessor\Context\CloseContext;
use Netlogix\XmlProcessor\NodeProcessor\NodeProcessorInterface;
use Netlogix\XmlProcessor\XmlProcessorContext;
use PHPUnit\Framework\TestCase;

class
XmlProcessorContextTest extends TestCase
{
    /** @var NodeProcessorInterface */
    static private $processor;

    function setUp(): void
    {
        parent::setUp();
        self::$processor = TestCase::getMockBuilder(NodeProcessorInterface::class)->getMock();
    }

    function test__construct(): void
    {
        $context = new XmlProcessorContext($this->getXMLReaderMock(), [], fn() => true);
        $this->assertInstanceOf(XmlProcessorContext::class, $context);
    }

    function testGetXMLReader(): void
    {
        $xmlReader = $this->getXMLReaderMock();
        $context = new XmlProcessorContext($xmlReader, [], fn() => true);
        $this->assertSame($xmlReader, $context->getXMLReader());
    }

    /**
     * @dataProvider getProcessorDataProvider
     */
    function testGetProcessor($processor, $expected): void
    {
        $context = new XmlProcessorContext($this->getXMLReaderMock(), [$processor], fn() => true);
        $this->assertSame($expected, $context->getProcessor(NodeProcessorInterface::class));
    }

    public static function getProcessorDataProvider(): \Generator
    {
        yield [self::$processor, self::$processor];
        yield [[], NULL];
    }

    /**
     * @dataProvider skipCurrentNodeDataProvider
     */
    public function testSkipCurrentNode(bool $return): void
    {
        $skipNodeMock = $this->getMockBuilder(\stdClass::class)->addMethods(['skipNode'])->getMock();
        $skipNodeMock->expects($this->atLeastOnce())->method('skipNode')->willReturn($return);
        $context = new XmlProcessorContext($this->getXMLReaderMock(), [], fn() => $skipNodeMock->skipNode());
        self::assertEquals($context->skipCurrentNode(), $return);
    }

    function skipCurrentNodeDataProvider(): iterable
    {
        yield [true];
        yield [false];
    }

    private function getXMLReaderMock(): \XMLReader
    {
        return $this->getMockBuilder(\XMLReader::class)->getMock();
    }
}
