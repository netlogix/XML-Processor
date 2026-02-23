<?php

declare(strict_types=1);

namespace Netlogix\XmlProcessor;

use XMLReader;
use Netlogix\XmlProcessor\NodeProcessor\Context\AbstractElementContext;
use Netlogix\XmlProcessor\NodeProcessor\Context\CloseContext;
use Netlogix\XmlProcessor\NodeProcessor\Context\NodeProcessorContext;
use Netlogix\XmlProcessor\NodeProcessor\Context\OpenContext;
use Netlogix\XmlProcessor\NodeProcessor\Context\TextContext;
use Netlogix\XmlProcessor\NodeProcessor\NodeProcessorInterface;

class XmlProcessor
{
    public const
        EVENT_OPEN_FILE = 'openFile',
        EVENT_END_OF_FILE = 'endOfFile',
        NODE_TYPE_NONE = 'NodeType_' . XMLReader::NONE,
        NODE_TYPE_ELEMENT = 'NodeType_' . XMLReader::ELEMENT,
        NODE_TYPE_ATTRIBUTE = 'NodeType_' . XMLReader::ATTRIBUTE,
        NODE_TYPE_TEXT = 'NodeType_' . XMLReader::TEXT,
        NODE_TYPE_CDATA = 'NodeType_' . XMLReader::CDATA,
        NODE_TYPE_ENTITY_REF = 'NodeType_' . XMLReader::ENTITY_REF,
        NODE_TYPE_ENTITY = 'NodeType_' . XMLReader::ENTITY,
        NODE_TYPE_PI = 'NodeType_' . XMLReader::PI,
        NODE_TYPE_COMMENT = 'NodeType_' . XMLReader::COMMENT,
        NODE_TYPE_DOC = 'NodeType_' . XMLReader::DOC,
        NODE_TYPE_DOC_TYPE = 'NodeType_' . XMLReader::DOC_TYPE,
        NODE_TYPE_DOC_FRAGMENT = 'NodeType_' . XMLReader::DOC_FRAGMENT,
        NODE_TYPE_NOTATION = 'NodeType_' . XMLReader::NOTATION,
        NODE_TYPE_WHITESPACE = 'NodeType_' . XMLReader::WHITESPACE,
        NODE_TYPE_SIGNIFICANT_WHITESPACE = 'NodeType_' . XMLReader::SIGNIFICANT_WHITESPACE,
        NODE_TYPE_END_ELEMENT = 'NodeType_' . XMLReader::END_ELEMENT,
        NODE_TYPE_END_ENTITY = 'NodeType_' . XMLReader::END_ENTITY,
        NODE_TYPE_XML_DECLARATION = 'NodeType_' . XMLReader::XML_DECLARATION;

    private array $nodePath = [];
    private string $currentValue = '';

    private ?array $skipNodes = null;
    private array $eventCache = [];

    private XMLReader $xml;
    private XmlProcessorContext $context;

    /** @var iterable<NodeProcessorInterface> */
    private iterable $processors;

    /** @var iterable<bool> */
    private iterable $parserProperties;

    /**
     * @var string[]
     */
    private ?array $whitelistEvents = null;

    private bool $skipCurrentNode = false;
    private bool $selfClosing = false;

    /**
     * @param iterable<NodeProcessorInterface> $processors
     * @param iterable<bool> $parserProperties
     */
    public function __construct(
        iterable $processors,
        iterable $parserProperties = [
            XMLReader::VALIDATE => false
        ]
    ) {
        $this->xml = new XMLReader();
        $this->processors = $processors;
        $this->parserProperties = $parserProperties;
        $this->context = new XmlProcessorContext($this->xml, $this->processors, fn () => $this->skipCurrentNode = true);
    }

    function setSkipNodes(?array $skipNodes = null): void
    {
        $this->skipNodes = $skipNodes;
    }

    function getSkipNodes(): ?array
    {
        return $this->skipNodes;
    }

    function setWhitelistEvents(?array $whitelistEvents = null): void
    {
        $this->whitelistEvents = $whitelistEvents;
    }

    function getWhitelistEvents(): ?array
    {
        return $this->whitelistEvents;
    }

    function getProcessor(string $processorName): ?NodeProcessorInterface
    {
        return $this->context->getProcessor($processorName);
    }

    public function processFile(string $filename): void
    {
        $this->xml->open($filename);
        foreach ($this->parserProperties as $parserProperty => $value) {
            $this->xml->setParserProperty($parserProperty, $value);
        }
        $this->callProcessorEvents(self::EVENT_OPEN_FILE);
        while ($this->xml->read()) {
            switch ($this->xml->nodeType) {
                case XMLReader::END_ELEMENT:
                    $this->eventCloseElement();
                    break;
                case XMLReader::ELEMENT:
                    $this->selfClosing = $this->xml->isEmptyElement;
                    $this->eventOpenElement();
                    $skip = $this->shouldSkipNode();
                    if ($skip) {
                        $this->xml->next();
                    }
                    if ($skip || $this->selfClosing) {
                        $this->eventCloseElement();
                    }
                    break;
                case XMLReader::TEXT:
                    $this->eventTextElement();
                    break;
                default:
                    $this->callProcessorEvents('NodeType_' . $this->xml->nodeType);
                    break;
            }
        }
        $this->callProcessorEvents(self::EVENT_END_OF_FILE);
        $this->xml->close();
    }

    private function skipNode(): bool
    {
        $result = $this->xml->next();
        $this->eventCloseElement();

        return $result;
    }

    private function shouldSkipNode(): bool
    {
        if ($this->skipCurrentNode) {
            $this->skipCurrentNode = false;

            return true;
        }
        if ($this->skipNodes === null) {
            return false;
        }
        $nodePath = \implode('/', $this->nodePath);
        foreach ($this->skipNodes as $skipNode) {
            if (self::checkNodePath($nodePath, $skipNode)) {
                return true;
            }
        }

        return false;
    }

    private function eventOpenElement(): void
    {
        $this->pushNodePath();
        $this->callProcessorEvents(XmlProcessor::NODE_TYPE_ELEMENT, OpenContext::class);
    }

    private function eventTextElement(): void
    {
        $this->currentValue = $this->xml->value;
        $this->callProcessorEvents(XmlProcessor::NODE_TYPE_TEXT, TextContext::class);
    }

    private function eventCloseElement(): void
    {
        $this->callProcessorEvents(XmlProcessor::NODE_TYPE_END_ELEMENT, CloseContext::class);
        $this->popNodePath();
    }

    private function callProcessorEvents(string $event, string $contextClass = NodeProcessorContext::class): void
    {
        if ($this->whitelistEvents !== null && !\in_array($event, $this->whitelistEvents, true)) {
            return;
        }
        $context = null;
        foreach ($this->getProcessorForEvent($event) as $action) {
            \call_user_func($action, $context ??= $this->createContext($contextClass));
        }
        unset($context);
    }

    /**
     * @return iterable<callable>
     */
    private function getProcessorForEvent(string $event): iterable
    {
        $nodePath = \implode('/', $this->nodePath);

        if (!\is_array($this->eventCache[$nodePath][$event] ?? false)) {
            $this->eventCache[$nodePath][$event] = [];
            foreach ($this->processors as $processor) {
                foreach ($processor->getSubscribedEvents($nodePath, $this->context) as $e => $action) {
                    if ($e !== $event) {
                        continue;
                    }
                    $this->eventCache[$nodePath][$event][] = $action;
                }
            }
        }

        yield from $this->eventCache[$nodePath][$event];
    }

    /**
     * @return array<string>
     */
    private function getAttributes(): array
    {
        $attributes = [];
        while ($this->xml->moveToNextAttribute()) {
            $attributes[$this->xml->name] = $this->xml->value;
        }

        return $attributes;
    }

    private function pushNodePath(): void
    {
        $this->nodePath[] = $this->xml->name;
    }

    private function popNodePath(): void
    {
        \array_pop($this->nodePath);
    }

    private function createContext(string $contextClass): NodeProcessorContext
    {
        $context = new $contextClass($this->context, $this->nodePath);
        if ($context instanceof AbstractElementContext) {
            $context->setSelfClosing($this->selfClosing);
        }
        if ($context instanceof OpenContext) {
            $context->setAttributes($this->getAttributes());
        } elseif ($context instanceof TextContext) {
            $context->setText($this->currentValue);
        }

        return $context;
    }

    static function checkNodePath(string $nodePath, string $expected): bool
    {
        return $nodePath === $expected
            || '/' . $nodePath === $expected
            || \str_ends_with($nodePath, $expected);
    }
}
