<?php

namespace Netlogix\XmlProcessor\Behat\NodeProcessor;

use Netlogix\XmlProcessor\NodeProcessor\AbstractNodeProcessor;
use Netlogix\XmlProcessor\NodeProcessor\Context\OpenContext;
use Netlogix\XmlProcessor\NodeProcessor\OpenNodeProcessorInterface;

class TestNodeProcessor extends AbstractNodeProcessor implements OpenNodeProcessorInterface
{
    const NODE_PATH = 'test';

    public array $data=[];

    function openElement(OpenContext $context)
    {
        $this->data[] = $context->getCurrentNodeName();
    }
}
