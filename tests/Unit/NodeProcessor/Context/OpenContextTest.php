<?php

namespace Netlogix\XmlProcessor\Tests\Unit\NodeProcessor\Context;

use Netlogix\XmlProcessor\NodeProcessor\Context\NodeProcessorContext;
use Netlogix\XmlProcessor\NodeProcessor\Context\OpenContext;
use Netlogix\XmlProcessor\XmlProcessorContext;
use PHPUnit\Framework\TestCase;

class OpenContextTest extends TestCase
{
    private function getOpenContext(
        ?XmlProcessorContext $context = NULL,
        array $nodePath = ['foo', 'bar']
    ): OpenContext
    {
        return new OpenContext(
            $context ?? $this->getMockBuilder(XmlProcessorContext::class)
            ->disableOriginalConstructor()
            ->getMock(),
            $nodePath
        );
    }

    public function test__construct(): void
    {
        $nodeProcessorContext = $this->getOpenContext();
        self::assertInstanceOf(OpenContext::class, $nodeProcessorContext);
    }

    public function testSetAttributes()
    {
        $context = $this->getOpenContext();
        $context->setAttributes(['foo' => 'bar']);
        self::assertEquals(['foo' => 'bar'], $context->getAttributes());
    }

    public function testGetAttributes()
    {
        $context = $this->getOpenContext();
        $context->setAttributes(['foo' => 'bar']);
        self::assertEquals(['foo' => 'bar'], $context->getAttributes());
    }

}
