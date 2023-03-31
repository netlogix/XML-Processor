<?php

namespace Netlogix\XmlProcessor\NodeProcessor;

use Netlogix\XmlProcessor\XmlProcessorContext;

class AbstractNodeProcessor implements NodeProcessorInterface
{
    function getNodePath(): string
    {
        if (!defined(static::class . '::NODE_PATH')) {
            throw new \Exception('NODE_PATH not defined in ' . static::class);
        }
        return static::NODE_PATH;
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

    function isNode(string $nodePath): bool
    {
        if ('/' . $nodePath == $this->getNodePath()) {
            return true;
        }
        return $nodePath === $this->getNodePath() || str_ends_with($nodePath, $this->getNodePath());
    }
}
