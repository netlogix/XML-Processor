<?php
declare(strict_types=1);

namespace Netlogix\XmlProcessor\Tests\Unit\NodeProcessor;

use Netlogix\XmlProcessor\NodeProcessor\AbstractNodeProcessor;
use Netlogix\XmlProcessor\NodeProcessor\NodeProcessorInterface;
use Netlogix\XmlProcessor\Tests\Fixtures\AbstractNodeProcessorTest\NodePathNodeProcessor;
use Netlogix\XmlProcessor\Tests\Fixtures\AbstractNodeProcessorTest\TestNodeProcessor;
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
        $this->assertEquals($expectedResult, $nodeProcessor->isNode($nodePath));
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
