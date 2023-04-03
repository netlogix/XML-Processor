<?php
declare(strict_types=1);

namespace Netlogix\XmlProcessor\NodeProcessor;

use Netlogix\XmlProcessor\NodeProcessor\Context\CloseContext;

interface CloseNodeProcessorInterface
{
    public function closeElement(CloseContext $context): void;
}
