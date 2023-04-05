<?php

namespace Netlogix\XmlProcessor\Tests\Unit\NodeProcessor\Context;

use Netlogix\XmlProcessor\NodeProcessor\Context\TextContext;
use Netlogix\XmlProcessor\XmlProcessorContext;
use PHPUnit\Framework\TestCase;

class TextContextTest extends TestCase
{
    private function getTextContext(
        ?XmlProcessorContext $context = NULL,
        array $nodePath = ['foo', 'bar']
    ): TextContext
    {
        return new TextContext(
            $context ?? $this->getMockBuilder(XmlProcessorContext::class)
            ->disableOriginalConstructor()
            ->getMock(),
            $nodePath
        );
    }

    public function test__construct(): void
    {
        $nodeProcessorContext = $this->getTextContext();
        self::assertInstanceOf(TextContext::class, $nodeProcessorContext);
    }

    public function testSetText()
    {
        $context = $this->getTextContext();
        $context->setText('foo');
        self::assertEquals('foo', $context->getText());
    }

    public function testGetText()
    {
        $context = $this->getTextContext();
        $context->setText('bar');
        self::assertEquals('bar', $context->getText());
    }

}
