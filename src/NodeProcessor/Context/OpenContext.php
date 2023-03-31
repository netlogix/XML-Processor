<?php

namespace Netlogix\XmlProcessor\NodeProcessor\Context;

class OpenContext extends NodeProcessorContext
{
    private array $attributes = [];

    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
