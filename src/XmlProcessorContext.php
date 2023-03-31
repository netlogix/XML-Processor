<?php

namespace Netlogix\XmlProcessor;

use Netlogix\XmlProcessor\NodeProcessor\NodeProcessorInterface;

class XmlProcessorContext
{
    private \XMLReader $xml;
    private iterable $processors;

    function __construct(\XMLReader $xml, iterable $processors)
    {
        $this->xml = $xml;
        $this->processors = $processors;
    }

    function getProcessor(string $class): ?NodeProcessorInterface
    {
        foreach ($this->processors as $processor) {
            if ($processor instanceof $class) {
                return $processor;
            }
        }
        return NULL;
    }

    function getXMLReader(): \XMLReader
    {
        return $this->xml;
    }
}
