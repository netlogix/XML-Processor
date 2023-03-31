# XML-Processor

PHP XML-Processor based on XMLReader.

The [`XMLProcessor`] walks through the XML-file with the `\XMLReader` and fires events on each node of the `\XMLReader`.
So its ease to process huge XML files with low memory usage.

## Events

The following events are available:

| Event         | XMLReader NodeType<br>event const    | react on                          | callback arguments       |
|---------------|--------------------------------------|-----------------------------------|--------------------------|
| `openFile`    | `XmlProcessor::EVENT_OPEN_FILE`      | after open file before first read | [`NodeProcessorContext`] |
| `endOfFile`   | `XmlProcessor::EVENT_END_OF_FILE`    | after last read before close      | [`NodeProcessorContext`] |
| `NodeType_0`  | `\XMLReader::NONE`                   | No node type                      | [`NodeProcessorContext`] |
| `NodeType_1`  | `\XMLReader::ELEMENT`                | Start element                     | [`OpenContext`]          |
| `NodeType_2`  | `\XMLReader::ATTRIBUTE`              | Attribute node                    | [`NodeProcessorContext`] |
| `NodeType_3`  | `\XMLReader::TEXT`                   | Text node                         | [`TextContext`]          |
| `NodeType_4`  | `\XMLReader::CDATA`                  | CDATA node                        | [`NodeProcessorContext`] |
| `NodeType_5`  | `\XMLReader::ENTITY_REF`             | Entity Reference node             | [`NodeProcessorContext`] |
| `NodeType_6`  | `\XMLReader::ENTITY`                 | Entity Declaration node           | [`NodeProcessorContext`] |
| `NodeType_7`  | `\XMLReader::PI`                     | Processing Instruction node       | [`NodeProcessorContext`] |
| `NodeType_8`  | `\XMLReader::COMMENT`                | Comment node                      | [`NodeProcessorContext`] |
| `NodeType_9`  | `\XMLReader::DOC`                    | Document node                     | [`NodeProcessorContext`] |
| `NodeType_10` | `\XMLReader::DOC_TYPE`               | Document Type node                | [`NodeProcessorContext`] |
| `NodeType_11` | `\XMLReader::DOC_FRAGMENT`           | Document Fragment node            | [`NodeProcessorContext`] |
| `NodeType_12` | `\XMLReader::NOTATION`               | Notation node                     | [`NodeProcessorContext`] |
| `NodeType_13` | `\XMLReader::WHITESPACE`             | Whitespace node                   | [`NodeProcessorContext`] |
| `NodeType_14` | `\XMLReader::SIGNIFICANT_WHITESPACE` | Significant Whitespace node       | [`NodeProcessorContext`] |
| `NodeType_15` | `\XMLReader::END_ELEMENT`            | End Element                       | [`CloseContext`]         |
| `NodeType_16` | `\XMLReader::END_ENTITY`             | End Entity                        | [`NodeProcessorContext`] |
| `NodeType_17` | `\XMLReader::XML_DECLARATION`        | XML Declaration node              | [`NodeProcessorContext`] |

## How to use

To process an XML file, you need to create a nodeProcessor class.
It has to implement the [`NodeProcessorInterface`].

Where you can define `NodeProcessorInterface::getSubscribedEvents` on which events you want to react.

For easier use, you can extend the [`AbstractNodeProcessor`] class and implement one of the following interfaces:

| Interface                       | description                   |
|---------------------------------|-------------------------------|
| [`OpenNodeProcessorInterface`]  | To react on opening tags      |
| [`CloseNodeProcessorInterface`] | To react on closing tags      |
| [`TextNodeProcessorInterface`]  | To react on text between tags |

## Example

To extract all values of `<test>` nodes of the following XML:

**file.xml**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <value>foo</value>
    <value>bar</value>
    <value>baz</value>
</root>
```

Create a simple nodeProcessor class which collect all values of the `<value>` nodes.

**OpenTestNodeProcessor.php**

```php
use Netlogix\XmlProcessor\NodeProcessor\AbstractNodeProcessor;
use Netlogix\XmlProcessor\NodeProcessor\OpenNodeProcessorInterface;
use Netlogix\XmlProcessor\NodeProcessor\Context\OpenContext;

class OpenValueNodeProcessor extends AbstractNodeProcessor implements OpenNodeProcessorInterface
{
    const NODE_PATH = 'value';
    private $nodeValues = [];

    public function openElement(OpenContext $context)
    {
        $xml = $context->getXmlProcessorContext()->getXmlReader();
        $node = $xml->expand();
        $this->nodeValues[] = $node->nodeValue;
    }

    function getNodeValues(): array
    {
        return $this->nodeValues;
    }
}
```

Create a new instance of the [`XmlProcessor`] class and attach the new nodeProcessor.

```php
require_once 'OpenTestNodeProcessor.php';

require_once 'vendor/autoload.php';

$valueNodeProcessor = new OpenValueNodeProcessor();
$processor = new XMLProcessor([$valueNodeProcessor]);
$processor->processFile('file.xml');

var_dump($valueNodeProcessor->getNodeValues());
```

**result:**

```php
array(3) {
  [0]=>
  string(3) "foo"
  [1]=>
  string(3) "bar"
  [2]=>
  string(3) "baz"
}
```

[`XmlProcessor`]: src/XmlProcessor.php

[`NodeProcessorInterface`]: src/NodeProcessor/NodeProcessorInterface.php

[`AbstractNodeProcessor`]: src/NodeProcessor/AbstractNodeProcessor.php

[`OpenNodeProcessorInterface`]: src/NodeProcessor/OpenNodeProcessorInterface.php

[`CloseNodeProcessorInterface`]: src/NodeProcessor/CloseNodeProcessorInterface.php

[`TextNodeProcessorInterface`]: src/NodeProcessor/TextNodeProcessorInterface.php

[`NodeProcessorContext`]: src/NodeProcessor/Context/NodeProcessorContext.php

[`OpenContext]: src/NodeProcessor/Context/OpenContext.php

[`TextContext`]: src/NodeProcessor/Context/TextContext.php

[`CloseContext`]: src/NodeProcessor/Context/CloseContext.php