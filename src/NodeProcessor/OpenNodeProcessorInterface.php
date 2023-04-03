<?php
declare(strict_types=1);

namespace Netlogix\XmlProcessor\NodeProcessor;

use Netlogix\XmlProcessor\NodeProcessor\Context\OpenContext;

interface OpenNodeProcessorInterface
{
    public function openElement(OpenContext $context): void;
}
