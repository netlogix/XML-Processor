<?php
declare(strict_types=1);

namespace Netlogix\XmlProcessor\NodeProcessor;

use Netlogix\XmlProcessor\XmlProcessor;
use Netlogix\XmlProcessor\XmlProcessorContext;

class AbstractNodeProcessor implements NodeProcessorInterface
{
    public function getNodePath(): string
    {
        $constName = get_class($this) . '::NODE_PATH';
        if (!defined($constName)) {
            throw new \Exception('NODE_PATH not defined in ' . static::class);
        }
        return constant($constName);
    }

    public function getSubscribedEvents(string $nodePath, XmlProcessorContext $context): \Iterator
    {
        if ($this->isNode($nodePath)) {
            if ($this instanceof OpenNodeProcessorInterface) {
                yield 'NodeType_' . \XMLReader::ELEMENT => [$this, 'openElement'];
            }
            if ($this instanceof CloseNodeProcessorInterface) {
                yield 'NodeType_' . \XMLReader::END_ELEMENT => [$this, 'closeElement'];
            }
            if ($this instanceof TextNodeProcessorInterface) {
                yield 'NodeType_' . \XMLReader::TEXT => [$this, 'textElement'];
            }
        }
        yield from [];
    }

    public function isNode(string $nodePath): bool
    {
        return XmlProcessor::checkNodePath($nodePath, $this->getNodePath());
    }
}
