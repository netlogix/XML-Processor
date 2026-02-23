<?php

declare(strict_types = 1);

namespace Netlogix\XmlProcessor\Tests\Unit\NodeProcessor\Context;

use Generator;
use Netlogix\XmlProcessor\NodeProcessor\Context\AbstractElementContext;
use Netlogix\XmlProcessor\XmlProcessorContext;
use PHPUnit\Framework\TestCase;

class AbstractElementContextTest extends TestCase
{
    private function getCloseContext(
        ?XmlProcessorContext $context = null,
        array $nodePath = ['foo', 'bar']
    ): AbstractElementContext {
        return new AbstractElementContext(
            $context ?? $this->getMockBuilder(XmlProcessorContext::class)->disableOriginalConstructor()->getMock(),
            $nodePath
        );
    }

    public function test__construct(): void
    {
        $nodeProcessorContext = $this->getCloseContext();
        self::assertInstanceOf(AbstractElementContext::class, $nodeProcessorContext);
    }

    /**
     * @dataProvider setSelfClosingDataProvider
     */
    function testSetSelfClosing($set, $expect): void
    {
        $nodeProcessorContext = $this->getCloseContext();
        $nodeProcessorContext->setSelfClosing($set);
        self::assertEquals($expect, $nodeProcessorContext->getSelfClosing());
    }

    function setSelfClosingDataProvider(): Generator
    {
        yield [true, true];
        yield [false, false];
    }

    /**
     * @dataProvider getSelfClosingDataProvider
     */
    function testGetSelfClosing($set, $expect): void
    {
        $nodeProcessorContext = $this->getCloseContext();
        if ($set !== null) {
            $nodeProcessorContext->setSelfClosing($set);
        }
        self::assertEquals($expect, $nodeProcessorContext->getSelfClosing());
    }

    function getSelfClosingDataProvider(): Generator
    {
        yield [null, false];
        yield [true, true];
        yield [false, false];
    }
}
