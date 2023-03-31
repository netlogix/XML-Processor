<?php

namespace Netlogix\XmlProcessor\NodeProcessor\Context;

class TextContext extends NodeProcessorContext
{
    private string $text;

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function getText(): string
    {
        return $this->text;
    }
}
