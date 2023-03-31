<?php

namespace Netlogix\XmlProcessor\NodeProcessor;

use Netlogix\XmlProcessor\XmlProcessorContext;

interface NodeProcessorInterface
{
    /**
     * @return Closure[]
     */
    public function getSubscribedEvents(string $nodePath, XmlProcessorContext $context): \Iterator;
}
