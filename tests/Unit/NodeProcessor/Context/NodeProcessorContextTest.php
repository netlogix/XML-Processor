<?php
declare(strict_types=1);

namespace Netlogix\XmlProcessor\Tests\Unit\NodeProcessor\Context;

use Netlogix\XmlProcessor\NodeProcessor\Context\NodeProcessorContext;
use Netlogix\XmlProcessor\XmlProcessorContext;
use PHPUnit\Framework\TestCase;

class NodeProcessorContextTest extends TestCase
{
    private function getNodeProcessorContext(
        ?XmlProcessorContext $context = NULL,
        array $nodePath = ['foo', 'bar']
    ): NodeProcessorContext
    {
        return new NodeProcessorContext(
            $context ?? $this->getMockBuilder(XmlProcessorContext::class)
            ->disableOriginalConstructor()
            ->getMock(),
            $nodePath
        );
    }

    public function testGetCurrentNodeName(): void
    {
        self::assertEquals('bar', $this->getNodeProcessorContext()->getCurrentNodeName());
    }

    public function test__construct(): void
    {
        $nodeProcessorContext = $this->getNodeProcessorContext();
        self::assertInstanceOf(NodeProcessorContext::class, $nodeProcessorContext);
    }

    public function testGetXmlProcessorContext(): void
    {
        $context = $this->getMockBuilder(XmlProcessorContext::class)
            ->disableOriginalConstructor()
            ->getMock();

        self::assertEquals($context, $this->getNodeProcessorContext($context)->getXmlProcessorContext());
    }

    public function testGetNodePath(): void
    {
        self::assertEquals('foo/bar', $this->getNodeProcessorContext()->getNodePath());
    }

    public function testGetNodePathArray(): void
    {
        self::assertEquals(['foo', 'bar'], $this->getNodeProcessorContext()->getNodePathArray());
    }
}
