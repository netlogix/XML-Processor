<?php

namespace Netlogix\XmlProcessor\NodeProcessor;

use Netlogix\XmlProcessor\NodeProcessor\Context\OpenContext;

interface OpenNodeProcessorInterface
{
    public function openElement(OpenContext $context);
}
