<?php
declare(strict_types=1);

namespace Netlogix\XmlProcessor\Tests\Unit;

use Netlogix\XmlProcessor\NodeProcessor\CloseNodeProcessorInterface;
use Netlogix\XmlProcessor\NodeProcessor\Context\CloseContext;
use Netlogix\XmlProcessor\NodeProcessor\Context\OpenContext;
use Netlogix\XmlProcessor\NodeProcessor\Context\TextContext;
use Netlogix\XmlProcessor\NodeProcessor\NodeProcessorInterface;
use Netlogix\XmlProcessor\NodeProcessor\OpenNodeProcessorInterface;
use Netlogix\XmlProcessor\NodeProcessor\TextNodeProcessorInterface;
use Netlogix\XmlProcessor\Tests\Fixtures\AbstractNodeProcessorTest\TestNodeProcessor;
use Netlogix\XmlProcessor\XmlProcessor;
use PHPUnit\Framework\TestCase;

class XmlProcessorTest extends TestCase
{
    public function test__construct()
    {
        $xmlProcessor = new XmlProcessor(
            [
                $this->getMockForAbstractClass(NodeProcessorInterface::class)
            ],
            [
                \XMLReader::SUBST_ENTITIES => true
            ]
        );
        self::assertInstanceOf(XmlProcessor::class, $xmlProcessor);
    }

    public function testGetProcessor(): void
    {
        $nodeProcessor = $this->getMockForAbstractClass(TestNodeProcessor::class);
        $xmlProcessor = new XmlProcessor([$nodeProcessor]);

        self::assertInstanceOf(TestNodeProcessor::class, $xmlProcessor->getProcessor(TestNodeProcessor::class));
        self::assertInstanceOf(get_class($nodeProcessor), $xmlProcessor->getProcessor(TestNodeProcessor::class));
        self::assertNull($xmlProcessor->getProcessor(OpenNodeProcessorInterface::class));
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

        $xmlProcessor->setSkipNodes(['foo']);
        $xmlProcessor->processFile(__DIR__ . '/../Fixtures/XmlProcessorTest/test.xml');

        $xmlProcessor = new XmlProcessor(
            [$nodeProcessor],
            [\XMLReader::SUBST_ENTITIES => true]
        );
        $xmlProcessor->processFile(__DIR__ . '/../Fixtures/XmlProcessorTest/test.xml');
    }

    public function testProcessFile_skipCurrentNode()
    {
        $nodeProcessor = $this->getMockForAbstractClass(NodeProcessorInterface::class);

        $nodeProcessor->method('getSubscribedEvents')
            ->will(
                $this->returnCallback(fn() => yield from [
                    'NodeType_' . \XMLReader::ELEMENT => function (OpenContext $context) {
                        $context->getXmlProcessorContext()->skipCurrentNode();
                        self::assertNotEquals('bar', $context->getCurrentNodeName());
                    },
                ])
            );

        $xmlProcessor = new XmlProcessor([
            $nodeProcessor
        ]);

        $xmlProcessor->processFile(__DIR__ . '/../Fixtures/XmlProcessorTest/test.xml');
    }

    /**
     * @dataProvider checkNodePathDataProvider
     */
    function testCheckNodePath(string $nodePath, string $expected, bool $result): void
    {
        self::assertSame(XmlProcessor::checkNodePath($nodePath, $expected), $result);
    }

    public static function checkNodePathDataProvider(): \Generator
    {
        yield ['', 'foo/bar', false];
        yield ['/foo/bar', 'foo/bar', true];
        yield ['/foo', 'foo/bar', false];
        yield ['foo', 'foo/bar', false];
        yield ['bar', 'foo/bar', false];
        yield ['foo/bar', 'bar', true];
        yield ['foo/bar', 'foo/bar', true];
        yield ['foo/bar/baz', 'foo/bar', false];
    }

    function testSetSkipNodes(): void
    {
        $xmlProcessor = new XmlProcessor([
            $this->getMockForAbstractClass(NodeProcessorInterface::class)
        ]);
        $xmlProcessor->setSkipNodes(['foo']);
        self::assertSame(['foo'], $xmlProcessor->getSkipNodes());
    }

    function testGetSkipNodes(): void
    {
        $xmlProcessor = new XmlProcessor([
            $this->getMockForAbstractClass(NodeProcessorInterface::class)
        ]);
        self::assertNull($xmlProcessor->getSkipNodes());
        $xmlProcessor->setSkipNodes([]);
        self::assertSame([], $xmlProcessor->getSkipNodes());
        $xmlProcessor->setSkipNodes(['foo']);
        self::assertSame(['foo'], $xmlProcessor->getSkipNodes());
    }

}
