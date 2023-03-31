<?php

namespace Netlogix\XmlProcessor;

use Netlogix\XmlProcessor\NodeProcessor\Context\CloseContext;
use Netlogix\XmlProcessor\NodeProcessor\Context\NodeProcessorContext;
use Netlogix\XmlProcessor\NodeProcessor\Context\OpenContext;
use Netlogix\XmlProcessor\NodeProcessor\Context\TextContext;
use Netlogix\XmlProcessor\NodeProcessor\NodeProcessorInterface;

class XmlProcessor
{
    const
        EVENT_OPEN_FILE = 'openFile',
        EVENT_END_OF_FILE = 'endOfFile';
    private array $nodePath = [];
    private string $currentValue = '';

    private ?array $attributes = NULL;

    private \XMLReader $xml;
    private XmlProcessorContext $context;

    /** @var NodeProcessorInterface[] */
    private iterable $processors;

    /**
     * @param NodeProcessorInterface[] $processors
     * @param bool[] $options
     */
    function __construct(
        iterable $processors,
        iterable $options = []
    )
    {
        $this->xml = new \XMLReader();
        foreach ($options as $option => $value) {
            $this->xml->setParserProperty($option, $value);
        }
        $this->processors = $processors;
        $this->context = new XmlProcessorContext($this->xml, $this->processors);
    }

    function processFile(string $filename)
    {
        $this->xml->open($filename);
        $this->getProcessorEvents(self::EVENT_OPEN_FILE);
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
                    break;
                default:
                    $this->getProcessorEvents('NodeType_' . $this->xml->nodeType);
                    break;
            }
        }
        $this->getProcessorEvents(self::EVENT_END_OF_FILE);
        $this->xml->close();
    }

    private function eventOpenElement()
    {
        $this->pushNodePath();
        $this->getProcessorEvents('NodeType_' . \XMLReader::ELEMENT, OpenContext::class);
    }

    private function eventTextElement()
    {
        $this->currentValue = $this->xml->value;
        $this->getProcessorEvents('NodeType_' . \XMLReader::TEXT, TextContext::class);
    }

    private function eventCloseElement()
    {
        $this->getProcessorEvents('NodeType_' . \XMLReader::END_ELEMENT, CloseContext::class);
        $this->popNodePath();
    }

    private function getProcessorEvents(string $event, string $contextClass = NodeProcessorContext::class)
    {
        $context = NULL;
        foreach ($this->getProcessorForEvent($event) as $action) {
            call_user_func($action, $context = $context ?? $this->createContext($contextClass));
        }
        unset($context);
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
