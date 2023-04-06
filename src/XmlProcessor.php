<?php
declare(strict_types=1);

namespace Netlogix\XmlProcessor;

use Netlogix\XmlProcessor\NodeProcessor\Context\CloseContext;
use Netlogix\XmlProcessor\NodeProcessor\Context\NodeProcessorContext;
use Netlogix\XmlProcessor\NodeProcessor\Context\OpenContext;
use Netlogix\XmlProcessor\NodeProcessor\Context\TextContext;
use Netlogix\XmlProcessor\NodeProcessor\NodeProcessorInterface;

class XmlProcessor
{
    public const
        EVENT_OPEN_FILE = 'openFile',
        EVENT_END_OF_FILE = 'endOfFile';
    private array $nodePath = [];
    private string $currentValue = '';

    private ?array $skipNodes = NULL;

    private \XMLReader $xml;
    private XmlProcessorContext $context;

    /** @var iterable<NodeProcessorInterface> */
    private iterable $processors;

    /** @var iterable<bool> */
    private iterable $parserProperties;

    private bool $skipCurrentNode = false;
    private bool $selfClosing = false;

    /**
     * @param iterable<NodeProcessorInterface> $processors
     * @param iterable<bool> $parserProperties
     */
    public function __construct(
        iterable $processors,
        iterable $parserProperties = []
    )
    {
        $this->xml = new \XMLReader();
        $this->processors = $processors;
        $this->parserProperties = $parserProperties;
        $this->context = new XmlProcessorContext(
            $this->xml,
            $this->processors,
            fn() => $this->skipCurrentNode = true
        );
    }

    function setSkipNodes(?array $skipNodes = NULL): void
    {
        $this->skipNodes = $skipNodes;
    }

    function getSkipNodes(): ?array
    {
        return $this->skipNodes;
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
        $this->getProcessorEvents(self::EVENT_OPEN_FILE);
        while ($this->xml->read()) {
            switch ($this->xml->nodeType) {
                case \XMLReader::END_ELEMENT:
                    $this->eventCloseElement();
                    break;
                case \XMLReader::ELEMENT:
                    $this->selfClosing = $this->xml->isEmptyElement;
                    $this->eventOpenElement();
                    if ($skip = $this->shouldSkipNode()) {
                        $this->xml->next();
                    }
                    if ($skip || $this->selfClosing) {
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
        if ($this->skipNodes === NULL) {
            return false;
        }
        $nodePath = implode('/', $this->nodePath);
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
        $this->getProcessorEvents('NodeType_' . \XMLReader::ELEMENT, OpenContext::class);
    }

    private function eventTextElement(): void
    {
        $this->currentValue = $this->xml->value;
        $this->getProcessorEvents('NodeType_' . \XMLReader::TEXT, TextContext::class);
    }

    private function eventCloseElement(): void
    {
        $this->getProcessorEvents('NodeType_' . \XMLReader::END_ELEMENT, CloseContext::class);
        $this->popNodePath();
    }

    private function getProcessorEvents(string $event, string $contextClass = NodeProcessorContext::class): void
    {
        $context = NULL;
        foreach ($this->getProcessorForEvent($event) as $action) {
            call_user_func($action, $context = $context ?? $this->createContext($contextClass));
        }
        unset($context);
    }

    /**
     * @return iterable<callable>
     */
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
        array_pop($this->nodePath);
    }

    private function createContext(string $contextClass): NodeProcessorContext
    {
        $context = new $contextClass($this->context, $this->nodePath);
        if (method_exists($context, 'setSelfClosing')) {
            $context->setSelfClosing($this->selfClosing);
        }
        if (method_exists($context, 'setAttributes')) {
            $context->setAttributes($this->getAttributes());
        }
        if (method_exists($context, 'setText')) {
            $context->setText($this->currentValue);
        }
        return $context;
    }

    static function checkNodePath(string $nodePath, string $expected): bool
    {
        return
            $expected === '/' . $nodePath ||
            $nodePath === $expected || (
            function_exists('str_end_with')
                ? str_end_with($nodePath, $expected) :
                substr_compare($nodePath, $expected, -strlen($expected)) === 0
            );
    }
}
