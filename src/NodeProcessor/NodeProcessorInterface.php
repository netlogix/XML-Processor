<?php
declare(strict_types=1);

namespace Netlogix\XmlProcessor\NodeProcessor;

use Netlogix\XmlProcessor\XmlProcessorContext;

interface NodeProcessorInterface
{
    /**
     * @return iterable<Closure>
     */
    public function getSubscribedEvents(string $nodePath, XmlProcessorContext $context): iterable;
}
