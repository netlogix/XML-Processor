<?php
declare(strict_types=1);

namespace Netlogix\XmlProcessor\Tests\Unit\NodeProcessor\Context;

use Netlogix\XmlProcessor\NodeProcessor\Context\CloseContext;
use Netlogix\XmlProcessor\XmlProcessorContext;
use PHPUnit\Framework\TestCase;

class CloseContextTest extends TestCase
{
    private function getCloseContext(
        ?XmlProcessorContext $context = NULL,
        array $nodePath = ['foo', 'bar']
    ): CloseContext
    {
        return new CloseContext(
            $context ?? $this->getMockBuilder(XmlProcessorContext::class)
            ->disableOriginalConstructor()
            ->getMock(),
            $nodePath
        );
    }

    public function test__construct(): void
    {
        $nodeProcessorContext = $this->getCloseContext();
        self::assertInstanceOf(CloseContext::class, $nodeProcessorContext);
    }

}
