<?php
declare(strict_types=1);

namespace Netlogix\XmlProcessor;

use Netlogix\XmlProcessor\NodeProcessor\NodeProcessorInterface;

class XmlProcessorContext
{
    private \XMLReader $xml;
    /**
     * @var iterable<NodeProcessorInterface>
     */
    private iterable $processors;

    public function __construct(\XMLReader $xml, iterable $processors)
    {
        $this->xml = $xml;
        $this->processors = $processors;
    }

    public function getProcessor(string $class): ?NodeProcessorInterface
    {
        foreach ($this->processors as $processor) {
            if ($processor instanceof $class) {
                return $processor;
            }
        }
        return NULL;
    }

    public function getXMLReader(): \XMLReader
    {
        return $this->xml;
    }
}
