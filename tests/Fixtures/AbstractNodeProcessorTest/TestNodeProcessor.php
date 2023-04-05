<?php

namespace Netlogix\XmlProcessor\Tests\Fixtures\AbstractNodeProcessorTest;

use Netlogix\XmlProcessor\NodeProcessor\AbstractNodeProcessor;
use Netlogix\XmlProcessor\NodeProcessor\CloseNodeProcessorInterface;
use Netlogix\XmlProcessor\NodeProcessor\Context\CloseContext;
use Netlogix\XmlProcessor\NodeProcessor\Context\OpenContext;
use Netlogix\XmlProcessor\NodeProcessor\Context\TextContext;
use Netlogix\XmlProcessor\NodeProcessor\OpenNodeProcessorInterface;
use Netlogix\XmlProcessor\NodeProcessor\TextNodeProcessorInterface;

class TestNodeProcessor extends AbstractNodeProcessor implements OpenNodeProcessorInterface, TextNodeProcessorInterface, CloseNodeProcessorInterface
{
    const NODE_PATH = 'test';

    function openElement(OpenContext $context): void
    {
    }

    function textElement(TextContext $context): void
    {
    }

    function closeElement(CloseContext $context): void
    {
    }
}