<?php

namespace Netlogix\XmlProcessor;

use Netlogix\XmlProcessor\NodeProcessor\Context\CloseContext;
use Netlogix\XmlProcessor\NodeProcessor\Context\NodeProcessorContext;
use Netlogix\XmlProcessor\NodeProcessor\Context\OpenContext;
use Netlogix\XmlProcessor\NodeProcessor\Context\TextContext;
use Netlogix\XmlProcessor\NodeProcessor\NodeProcessorInterface;

class XmlProcessor
{
    private array $nodePath = [];
    private string $currentValue = '';

    private ?array $attributes = NULL;

    private \XMLReader $xml;
    private XmlProcessorContext $context;

    /** @var NodeProcessorInterface[] */
    private iterable $processors;

    /**
     * @param NodeProcessorInterface[] $processors
     */
    function __construct(
        iterable $processors
    )
    {
        $this->xml = new \XMLReader();
        $this->processors = $processors;
        $this->context = new XmlProcessorContext($this->xml, $this->processors);
    }

    function execute(string $filename)
    {
        $this->xml->open($filename);
        while ($this->xml->read()) {
            switch ($this->xml->nodeType) {
                case \XMLReader::END_ELEMENT:
                    $this->eventCloseElement();
                    break;
                case \XMLReader::ELEMENT:
                    $selfClosing = $this->xml->isEmptyElement;
                    $this->eventOpenElement();
                    if ($selfClosing) {
                        $this->eventCloseElement();
                    }
                    break;
                case \XMLReader::TEXT:
                    $this->eventTextElement();
            }
        }
        $this->eventEndOfFile();
        $this->xml->close();
    }

    private function eventOpenElement()
    {
        $this->pushNodePath();
        $this->runNodeTypeProcessors(\XMLReader::ELEMENT, OpenContext::class);
    }

    private function eventTextElement()
    {
        $this->currentValue = $this->xml->value;
        $this->runNodeTypeProcessors(\XMLReader::TEXT, TextContext::class);
    }

    private function eventCloseElement()
    {
        $this->runNodeTypeProcessors(\XMLReader::END_ELEMENT, CloseContext::class);
        $this->popNodePath();
    }

    private function eventEndOfFile()
    {
        foreach ($this->getProcessorForEvent('endOfFile') as $action) {
            call_user_func($action);
        }
    }

    private function getProcessorForEvent(string $event): iterable
    {
        $nodePath = implode('/', $this->nodePath);
        foreach ($this->processors as $processor) {
            foreach ($processor->getSubscribedEvents($nodePath, $this->context) as $e => $action) {
                if ($e === $event) {
                    yield $action;
                }
            }
        }
    }

    private function runNodeTypeProcessors(string $type, string $contextClass)
    {
        $context = NULL;
        foreach ($this->getProcessorForEvent('NodeType_' . $type) as $action) {
            call_user_func($action, $context ?? $context = $this->createContext($contextClass));
        }
        unset($context);
    }

    private function getAttributes(): array
    {
        if ($this->attributes !== NULL) {
            return $this->attributes;
        }
        $this->attributes = [];
        while ($this->xml->moveToNextAttribute()) {
            $this->attributes[$this->xml->name] = $this->xml->value;
        }
        return $this->attributes;
    }

    private function pushNodePath(): void
    {
        $this->nodePath[] = $this->xml->name;
    }

    private function popNodePath(): void
    {
        array_pop($this->nodePath);
    }

    private function createContext($contextClass): NodeProcessorContext
    {
        $context = new $contextClass($this->context, $this->nodePath);
        if (method_exists($context, 'setAttributes')) {
            $context->setAttributes($this->getAttributes());
        }
        if (method_exists($context, 'setText')) {
            $context->setText($this->currentValue);
        }
        return $context;
    }
}
