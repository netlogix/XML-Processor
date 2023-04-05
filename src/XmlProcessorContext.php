<?php
declare(strict_types=1);

namespace Netlogix\XmlProcessor;

use Netlogix\XmlProcessor\NodeProcessor\NamedNodeProcessorInterface;
use Netlogix\XmlProcessor\NodeProcessor\NodeProcessorInterface;

class XmlProcessorContext
{
    private \XMLReader $xml;
    /**
     * @var iterable<NodeProcessorInterface>
     */
    private iterable $processors;

    private \Closure $skipNode;

    public function __construct(\XMLReader $xml, iterable $processors, \Closure $skipNode)
    {
        $this->xml = $xml;
        $this->processors = $processors;
        $this->skipNode = $skipNode;
    }

    public function skipCurrentNode(): bool
    {
        return ($this->skipNode)();
    }

    public function getProcessor(string $class): ?NodeProcessorInterface
    {
        foreach ($this->processors as $processor) {
            if (class_exists($class) && $processor instanceof $class) {
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
