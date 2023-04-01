<?php
declare(strict_types=1);

namespace Netlogix\XmlProcessor\Behat\NodeProcessor;

use Netlogix\XmlProcessor\NodeProcessor\AbstractNodeProcessor;
use Netlogix\XmlProcessor\NodeProcessor\Context\OpenContext;
use Netlogix\XmlProcessor\NodeProcessor\Context\TextContext;
use Netlogix\XmlProcessor\NodeProcessor\OpenNodeProcessorInterface;
use Netlogix\XmlProcessor\NodeProcessor\TextNodeProcessorInterface;

class TextNodeProcess extends AbstractNodeProcessor implements TextNodeProcessorInterface, InvokeNodeProcessorInterface
{
    public const NODE_PATH = 'test';

    public array $data = [];

    public function __invoke(): array
    {
        return $this->data;
    }

    public function textElement(TextContext $context): void
    {
        $this->data[] = $context->getText();
    }

}
