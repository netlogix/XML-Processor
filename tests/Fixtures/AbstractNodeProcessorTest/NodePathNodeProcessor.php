<?php

namespace Netlogix\XmlProcessor\Tests\Fixtures\AbstractNodeProcessorTest;

use Netlogix\XmlProcessor\NodeProcessor\AbstractNodeProcessor;

class NodePathNodeProcessor extends AbstractNodeProcessor
{
    private string $nodePath = '';

    function getNodePath(): string
    {
        return $this->nodePath;
    }

    function setNodePath(string $nodePath): self
    {
        $this->nodePath = $nodePath;
        return $this;
    }
}