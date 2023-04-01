<?php
declare(strict_types=1);

namespace Netlogix\XmlProcessor\NodeProcessor;

use Netlogix\XmlProcessor\NodeProcessor\Context\TextContext;

interface TextNodeProcessorInterface
{
    public function textElement(TextContext $context): void;
}
