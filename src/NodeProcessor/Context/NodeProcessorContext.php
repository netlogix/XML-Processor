<?php
declare(strict_types=1);

namespace Netlogix\XmlProcessor\NodeProcessor\Context;

use Netlogix\XmlProcessor\XmlProcessorContext;

class NodeProcessorContext
{
    private XmlProcessorContext $xmlProcessorContext;

    /**
     * @var array<string>
     */
    private array $nodePath;

    /**
     * @param array<string> $nodePath
     */
    public function __construct(
        XmlProcessorContext $xmlProcessorContext,
        array $nodePath
    )
    {
        $this->xmlProcessorContext = $xmlProcessorContext;
        $this->nodePath = $nodePath;
    }

    public function getXmlProcessorContext(): XmlProcessorContext
    {
        return $this->xmlProcessorContext;
    }

    public function getCurrentNodeName(): ?string
    {
        return end($this->nodePath) ?: NULL;
    }

    public function getNodePath(): string
    {
        return implode('/', $this->nodePath);
    }

    /**
     * @return string[]
     */
    public function getNodePathArray(): array
    {
        return $this->nodePath;
    }
}
