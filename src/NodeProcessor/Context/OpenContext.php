<?php
declare(strict_types=1);

namespace Netlogix\XmlProcessor\NodeProcessor\Context;

class OpenContext extends NodeProcessorContext
{
    /**
     * @var array<string>
     */
    private array $attributes = [];

    /**
     * @param array<string> $attributes
     */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * @return array<string>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
