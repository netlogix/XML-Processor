<?php
declare(strict_types=1);

namespace Netlogix\XmlProcessor\Tests\Unit\NodeProcessor;

use Netlogix\XmlProcessor\NodeProcessor\AbstractNodeProcessor;
use Netlogix\XmlProcessor\NodeProcessor\CloseNodeProcessorInterface;
use Netlogix\XmlProcessor\NodeProcessor\Context\CloseContext;
use Netlogix\XmlProcessor\NodeProcessor\Context\OpenContext;
use Netlogix\XmlProcessor\NodeProcessor\Context\TextContext;
use Netlogix\XmlProcessor\NodeProcessor\NodeProcessorInterface;
use Netlogix\XmlProcessor\NodeProcessor\OpenNodeProcessorInterface;
use Netlogix\XmlProcessor\NodeProcessor\TextNodeProcessorInterface;
use Netlogix\XmlProcessor\Tests\Fixtures\AbstractNodeProcessorTest\NodePathNodeProcessor;
use Netlogix\XmlProcessor\Tests\Fixtures\AbstractNodeProcessorTest\TestNodeProcessor;
use Netlogix\XmlProcessor\XmlProcessorContext;
use PHPUnit\Framework\TestCase;

class AbstractNodeProcessorTest extends TestCase
{

    public function testGetNodePath(): void
    {
        $nodeProcessor = new TestNodeProcessor();
        $this->assertEquals(TestNodeProcessor::NODE_PATH, $nodeProcessor->getNodePath());

        $nodeProcessor = $this->getMockForAbstractClass(AbstractNodeProcessor::class);
        $this->expectException(\Exception::class);
        $nodeProcessor->getNodePath();
    }

    public function testGetSubscribedEvents(): void
    {
        $this->markTestSkipped('ToDo: Implement testGetSubscribedEvents()');
    }

    /**
     * @dataProvider isNodeDataProvider
     */
    public function testIsNode(NodeProcessorInterface $nodeProcessor, string $nodePath, bool $expectedResult): void
    {
        $nodeProcessor = $this->getMockForAbstractClass(
            TestNodeProcessor::class
        );
        $context = $this->createMock(XmlProcessorContext::class);
        $events = [];
        foreach ($nodeProcessor->getSubscribedEvents('test', $context) as $event => $action) {
            $events[] = $event;
            self::assertIsCallable($action);
        }

        self::assertEquals([
            'NodeType_' . \XMLReader::ELEMENT,
            'NodeType_' . \XMLReader::END_ELEMENT,
            'NodeType_' . \XMLReader::TEXT,
        ], $events);
    }

    public static function isNodeDataProvider(): \Generator
    {
        $nodeProcessor = new NodePathNodeProcessor();
        yield [$nodeProcessor->setNodePath('foo'), 'foo', true];
        yield [$nodeProcessor->setNodePath('foo'), 'bar', false];
        yield [$nodeProcessor->setNodePath('/foo'), 'foo', true];
        yield [$nodeProcessor->setNodePath('/foo'), 'bar', false];

    }
}
