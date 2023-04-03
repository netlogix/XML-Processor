<?php

namespace Netlogix\XmlProcessor\Tests\Unit\Behat\NodeProcessor;

use Netlogix\XmlProcessor\Behat\NodeProcessor\TextNodeProcessor;
use Netlogix\XmlProcessor\NodeProcessor\Context\TextContext;
use PHPUnit\Framework\TestCase;

class TextNodeProcessorTest extends TestCase
{

    function test__invoke(): void
    {
        $nodeProcessor = new TextNodeProcessor();
        self::assertIsArray($nodeProcessor());
    }

    function testTextElement(): void
    {
        $nodeProcessor = new TextNodeProcessor();
        $context = $this->getMockBuilder(TextContext::class)->disableOriginalConstructor()->getMock();
        $context->method('getText')->willReturnOnConsecutiveCalls(
            'foo',
            'bar'
        );
        $nodeProcessor->textElement($context);
        self::assertIsArray($nodeProcessor());
        self::assertEquals(['foo'], $nodeProcessor());
        $nodeProcessor->textElement($context);
        self::assertEquals(['foo', 'bar'], $nodeProcessor());
    }

}
