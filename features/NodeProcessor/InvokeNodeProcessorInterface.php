<?php
declare(strict_types=1);

namespace Netlogix\XmlProcessor\Behat\NodeProcessor;

use Netlogix\XmlProcessor\NodeProcessor\NodeProcessorInterface;

interface InvokeNodeProcessorInterface extends NodeProcessorInterface
{
    function __invoke(): array;
}