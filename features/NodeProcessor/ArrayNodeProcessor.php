<?php
declare(strict_types=1);

namespace Netlogix\XmlProcessor\Behat\NodeProcessor;

use Netlogix\XmlProcessor\NodeProcessor\Context\OpenContext;
use Netlogix\XmlProcessor\NodeProcessor\Context\TextContext;
use Netlogix\XmlProcessor\NodeProcessor\NodeProcessorInterface;
use Netlogix\XmlProcessor\XmlProcessorContext;

class ArrayNodeProcessor implements NodeProcessorInterface, InvokeNodeProcessorInterface
{
    private array $data = [];
    private array $currentElement = [];
    private int $currentLevel = 1;

    public function __invoke(): array
    {
        return $this->removeParent($this->data);
    }

    private function removeParent(array $nodes): array
    {
        foreach ($nodes as &$node) {
            unset($node['parent']);
            $node['children'] = $this->removeParent($node['children']);
        }
        return $nodes;
    }

    public function getSubscribedEvents(string $nodePath, XmlProcessorContext $context): iterable
    {
        yield 'NodeType_' . \XMLReader::ELEMENT => [$this, 'openElement'];
        yield 'NodeType_' . \XMLReader::TEXT => [$this, 'textElement'];
    }

    public function openElement(OpenContext $context): void
    {
        $currentLevel = count($context->getNodePathArray());
        $newValue = [
            'node' => $context->getCurrentNodeName(),
            'level' => $currentLevel,
            'attributes' => $context->getAttributes(),
            'children' => [],
        ];
        if (1 === $currentLevel && 1 === $this->currentLevel) {
            $newValue['parent'] = &$this->data;
        } elseif ($currentLevel > $this->currentLevel) {
            $newValue['parent'] = &$this->currentElement;
        } elseif ($currentLevel === $this->currentLevel) {
            $newValue['parent'] = &$this->currentElement['parent'];
        } elseif ($currentLevel < $this->currentLevel) {
            $newValue['parent'] = &$this->currentElement['parent']['parent'];
        }

        if (1 === $currentLevel) {
            $this->data[] =  &$newValue;
        } else {
            $newValue['parent']['children'][] = &$newValue;
        }
        $this->currentElement = &$newValue;
        $this->currentLevel = $currentLevel;
    }

    public function textElement(TextContext $context): void
    {
        $this->currentElement['text'] = $context->getText();
    }
}
