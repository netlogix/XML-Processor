<?php

namespace Netlogix\XmlProcessor\NodeProcessor\Context;

class AbstractElementContext extends NodeProcessorContext
{
    private bool $selfClosing = false;

    function setSelfClosing(bool $selfClosing): void
    {
        $this->selfClosing = $selfClosing;
    }

    function getSelfClosing(): bool
    {
        return $this->selfClosing;
    }
}
