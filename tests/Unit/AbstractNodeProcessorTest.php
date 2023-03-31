<?php

namespace Netlogix\XmlProcessor\NodeProcessor\Tests\Unit;

use Netlogix\XmlProcessor\NodeProcessor\AbstractNodeProcessor;
use Netlogix\XmlProcessor\NodeProcessor\NodeProcessorInterface;
use Netlogix\XmlProcessor\Tests\Fixtures\AbstractNodeProcessorTest\NodePathNodeProcessor;
use Netlogix\XmlProcessor\Tests\Fixtures\AbstractNodeProcessorTest\TestNodeProcessor;
use PHPUnit\Framework\TestCase;

class AbstractNodeProcessorTest extends TestCase
{

    public function testGetNodePath()
    {
        $nodeProcessor = new TestNodeProcessor();
        $this->assertEquals(TestNodeProcessor::NODE_PATH, $nodeProcessor->getNodePath());

        $nodeProcessor = $this->getMockForAbstractClass(AbstractNodeProcessor::class);
        $this->expectException(\Exception::class);
        $nodeProcessor->getNodePath();
    }

    public function testGetSubscribedEvents()
    {
        $this->markTestSkipped('ToDo: Implement testGetSubscribedEvents()');
    }

    /**
     * @dataProvider isNodeDataProvider
     */
    public function testIsNode(NodeProcessorInterface $nodeProcessor, string $nodePath, bool $expectedResult)
    {
        $this->assertEquals($expectedResult, $nodeProcessor->isNode($nodePath));
    }

    public static function isNodeDataProvider(): \Generator
    {
        $nodeProcessor = new NodePathNodeProcessor();
        yield [$nodeProcessor->setNodePath('foo'),'foo', true];
        yield [$nodeProcessor->setNodePath('foo'),'bar', false];
        yield [$nodeProcessor->setNodePath('/foo'),'foo', true];
        yield [$nodeProcessor->setNodePath('/foo'),'bar', false];

    }
}
